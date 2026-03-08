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

class BudgetItemManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_add_a_valid_item_to_a_budget(): void
    {
        $user = $this->createUser();
        $budget = $this->createBudget($user);
        $resource = $this->createResource('Cement', 'kg', 'Materials', 32.50);

        $response = $this
            ->actingAs($user)
            ->post(route('budgets.items.store', $budget), [
                'resource_id' => $resource->id,
                'description' => 'Main material',
                'quantity' => 3,
                'unit_price' => 32.50,
            ]);

        $response->assertRedirect(route('budgets.show', $budget));
        $this->assertDatabaseHas('budget_items', [
            'budget_id' => $budget->id,
            'resource_id' => $resource->id,
            'unit_id' => $resource->unit_id,
            'name' => 'Cement',
            'description' => 'Main material',
        ]);

        $budgetItem = BudgetItem::query()->firstOrFail();

        $this->assertSame('97.50', $budgetItem->subtotal);
        $this->assertSame('97.50', $budget->fresh()->total_cost);
    }

    public function test_a_user_can_edit_a_budget_item_and_the_totals_are_recalculated(): void
    {
        $user = $this->createUser();
        $budget = $this->createBudget($user);
        $resource = $this->createResource('Steel', 'kg', 'Materials', 18.00);

        $budgetItem = BudgetItem::query()->create([
            'budget_id' => $budget->id,
            'resource_id' => $resource->id,
            'unit_id' => $resource->unit_id,
            'name' => $resource->name,
            'description' => null,
            'quantity' => 2,
            'unit_price' => 18.00,
            'subtotal' => 36.00,
        ]);

        $budget->recalculateTotalCost();

        $response = $this
            ->actingAs($user)
            ->put(route('budgets.items.update', [$budget, $budgetItem]), [
                'resource_id' => $resource->id,
                'description' => 'Updated item',
                'quantity' => 4.5,
                'unit_price' => 20.00,
            ]);

        $response->assertRedirect(route('budgets.show', $budget));

        $budgetItem->refresh();

        $this->assertSame('90.00', $budgetItem->subtotal);
        $this->assertSame('90.00', $budget->fresh()->total_cost);
    }

    public function test_a_user_can_delete_a_budget_item_and_the_total_is_updated(): void
    {
        $user = $this->createUser();
        $budget = $this->createBudget($user);
        $resourceOne = $this->createResource('Bricks', 'u', 'Materials', 1.20);
        $resourceTwo = $this->createResource('Sand', 'm3', 'Materials', 45.00);

        BudgetItem::query()->create([
            'budget_id' => $budget->id,
            'resource_id' => $resourceOne->id,
            'unit_id' => $resourceOne->unit_id,
            'name' => $resourceOne->name,
            'description' => null,
            'quantity' => 10,
            'unit_price' => 1.20,
            'subtotal' => 12.00,
        ]);

        $budgetItem = BudgetItem::query()->create([
            'budget_id' => $budget->id,
            'resource_id' => $resourceTwo->id,
            'unit_id' => $resourceTwo->unit_id,
            'name' => $resourceTwo->name,
            'description' => null,
            'quantity' => 2,
            'unit_price' => 45.00,
            'subtotal' => 90.00,
        ]);

        $budget->recalculateTotalCost();

        $response = $this
            ->actingAs($user)
            ->delete(route('budgets.items.destroy', [$budget, $budgetItem]));

        $response->assertRedirect(route('budgets.show', $budget));
        $this->assertDatabaseMissing('budget_items', [
            'id' => $budgetItem->id,
        ]);
        $this->assertSame('12.00', $budget->fresh()->total_cost);
    }

    public function test_a_regular_user_cannot_modify_items_of_someone_elses_budget(): void
    {
        $owner = $this->createUser();
        $otherUser = $this->createUser('other@example.com');
        $budget = $this->createBudget($owner, 'BGT-OWNER');
        $resource = $this->createResource('Labor', 'hr', 'Workforce', 15.00);

        $budgetItem = BudgetItem::query()->create([
            'budget_id' => $budget->id,
            'resource_id' => $resource->id,
            'unit_id' => $resource->unit_id,
            'name' => $resource->name,
            'description' => null,
            'quantity' => 8,
            'unit_price' => 15.00,
            'subtotal' => 120.00,
        ]);

        $budget->recalculateTotalCost();

        $this->actingAs($otherUser)
            ->post(route('budgets.items.store', $budget), [
                'resource_id' => $resource->id,
                'description' => 'Unauthorized item',
                'quantity' => 1,
                'unit_price' => 15.00,
            ])
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->put(route('budgets.items.update', [$budget, $budgetItem]), [
                'resource_id' => $resource->id,
                'description' => 'Updated by stranger',
                'quantity' => 10,
                'unit_price' => 15.00,
            ])
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->delete(route('budgets.items.destroy', [$budget, $budgetItem]))
            ->assertForbidden();
    }

    public function test_budget_item_validation_fails_for_missing_or_invalid_values(): void
    {
        $user = $this->createUser();
        $budget = $this->createBudget($user);

        $response = $this
            ->actingAs($user)
            ->from(route('budgets.items.create', $budget))
            ->post(route('budgets.items.store', $budget), [
                'resource_id' => 999,
                'description' => 'Invalid item',
                'quantity' => 0,
                'unit_price' => -1,
            ]);

        $response
            ->assertRedirect(route('budgets.items.create', $budget))
            ->assertSessionHasErrors(['resource_id', 'quantity', 'unit_price']);
    }

    private function createUser(string $email = 'user@example.com'): User
    {
        return User::factory()->create([
            'email' => $email,
            'role' => User::ROLE_USER,
        ]);
    }

    private function createBudget(User $user, string $code = 'BGT-ITEM-001'): Budget
    {
        return Budget::query()->create([
            'user_id' => $user->id,
            'code' => $code,
            'title' => 'Budget with Items',
            'description' => 'Budget description',
            'budget_date' => '2026-03-08',
            'status' => Budget::STATUS_DRAFT,
            'is_published' => false,
            'total_cost' => 0,
        ]);
    }

    private function createResource(string $resourceName, string $unitSymbol, string $categoryName, float $unitPrice): Resource
    {
        $suffix = (string) random_int(1000, 9999);

        $category = Category::query()->create([
            'name' => $categoryName.' '.$suffix,
            'description' => $categoryName.' description',
        ]);

        $unit = Unit::query()->create([
            'name' => 'Unit '.$unitSymbol.' '.$suffix,
            'symbol' => substr($unitSymbol.$suffix, 0, 20),
        ]);

        return Resource::query()->create([
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'name' => $resourceName,
            'description' => $resourceName.' description',
            'unit_price' => $unitPrice,
        ]);
    }
}
