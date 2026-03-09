<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Category;
use App\Models\Resource;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBudgetPublicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_published_budgets_are_displayed_on_the_public_home_page(): void
    {
        $publishedBudget = $this->createBudget(
            $this->createUser('published-owner@example.com'),
            'BGT-PUB-001',
            'Published Budget',
            Budget::STATUS_PUBLISHED,
            true,
        );
        $privateBudget = $this->createBudget(
            $this->createUser('private-owner@example.com'),
            'BGT-PRV-001',
            'Private Budget',
            Budget::STATUS_DRAFT,
            false,
        );

        $response = $this->get(route('home'));

        $response
            ->assertOk()
            ->assertSee($publishedBudget->title)
            ->assertSee($publishedBudget->code)
            ->assertDontSee($privateBudget->title)
            ->assertDontSee($privateBudget->code);
    }

    public function test_public_budget_detail_is_available_only_for_published_budgets(): void
    {
        $publishedBudget = $this->createBudget(
            $this->createUser('detail-owner@example.com'),
            'BGT-PUB-002',
            'Public Detail Budget',
            Budget::STATUS_PUBLISHED,
            true,
        );
        $unit = Unit::query()->create([
            'name' => 'Kilogram',
            'symbol' => 'kg',
        ]);
        $category = Category::query()->create([
            'name' => 'Materials',
            'description' => 'Materials description',
        ]);
        $resource = Resource::query()->create([
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'name' => 'Cement',
            'description' => 'Cement description',
            'unit_price' => 25.50,
        ]);

        $parentItem = BudgetItem::query()->create([
            'budget_id' => $publishedBudget->id,
            'resource_id' => null,
            'unit_id' => $unit->id,
            'name' => 'Structural work',
            'description' => 'Grouped task',
            'quantity' => 1,
            'unit_price' => 0,
            'subtotal' => 0,
            'sort_order' => 1,
        ]);

        BudgetItem::query()->create([
            'budget_id' => $publishedBudget->id,
            'parent_id' => $parentItem->id,
            'resource_id' => $resource->id,
            'unit_id' => $unit->id,
            'name' => $resource->name,
            'description' => 'Main material',
            'quantity' => 3,
            'unit_price' => 25.50,
            'subtotal' => 76.50,
            'sort_order' => 1,
        ]);

        $publishedBudget->recalculateTotalCost();

        $this->get(route('budgets.public.show', $publishedBudget))
            ->assertOk()
            ->assertSee($publishedBudget->title)
            ->assertSee($publishedBudget->code)
            ->assertSee('N.º')
            ->assertSee('1.1')
            ->assertSee('Cement')
            ->assertSee('Main material')
            ->assertSee('Materials')
            ->assertSee('76.50')
            ->assertDontSee('Editar presupuesto');
    }

    public function test_public_budget_detail_returns_not_found_for_unpublished_budgets(): void
    {
        $budget = $this->createBudget(
            $this->createUser('draft-owner@example.com'),
            'BGT-PRV-002',
            'Draft Detail Budget',
            Budget::STATUS_DRAFT,
            false,
        );

        $this->get(route('budgets.public.show', $budget))
            ->assertNotFound();
    }

    public function test_an_unauthorized_user_cannot_change_a_budget_publication_status(): void
    {
        $owner = $this->createUser('owner@example.com');
        $intruder = $this->createUser('intruder@example.com');
        $budget = $this->createBudget($owner, 'BGT-PUB-003', 'Protected Budget', Budget::STATUS_DRAFT, false);

        $this->actingAs($intruder)
            ->patch(route('budgets.publication.update', $budget), [
                'published' => true,
            ])
            ->assertForbidden();

        $this->assertFalse($budget->fresh()->is_published);
        $this->assertSame(Budget::STATUS_DRAFT, $budget->fresh()->status);
    }

    public function test_an_authorized_user_can_publish_and_unpublish_a_budget(): void
    {
        $owner = $this->createUser('publisher@example.com');
        $budget = $this->createBudget($owner, 'BGT-PUB-004', 'Publication Toggle Budget', Budget::STATUS_DRAFT, false);

        $this->actingAs($owner)
            ->patch(route('budgets.publication.update', $budget), [
                'published' => true,
            ])
            ->assertRedirect(route('budgets.show', $budget))
            ->assertSessionHas('success', 'Presupuesto publicado correctamente.');

        $budget->refresh();

        $this->assertTrue($budget->is_published);
        $this->assertSame(Budget::STATUS_PUBLISHED, $budget->status);

        $this->actingAs($owner)
            ->patch(route('budgets.publication.update', $budget), [
                'published' => false,
            ])
            ->assertRedirect(route('budgets.show', $budget))
            ->assertSessionHas('success', 'Presupuesto ocultado correctamente.');

        $budget->refresh();

        $this->assertFalse($budget->is_published);
        $this->assertSame(Budget::STATUS_DRAFT, $budget->status);
    }

    private function createUser(string $email): User
    {
        return User::factory()->create([
            'email' => $email,
            'role' => User::ROLE_USER,
        ]);
    }

    private function createBudget(User $user, string $code, string $title, string $status, bool $isPublished): Budget
    {
        return Budget::query()->create([
            'user_id' => $user->id,
            'code' => $code,
            'title' => $title,
            'description' => $title.' description',
            'budget_date' => '2026-03-08',
            'status' => $status,
            'is_published' => $isPublished,
            'total_cost' => 1250.50,
        ]);
    }
}
