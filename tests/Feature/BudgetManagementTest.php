<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_authenticated_user_can_create_a_valid_budget(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('budgets.store'), [
                'title' => 'House Renovation',
                'description' => 'Base budget for renovation works',
                'budget_date' => '2026-03-08',
                'status' => Budget::STATUS_DRAFT,
            ]);

        $response->assertRedirect(route('budgets.index'));
        $budget = Budget::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertDatabaseHas('budgets', [
            'user_id' => $user->id,
            'title' => 'House Renovation',
            'status' => Budget::STATUS_DRAFT,
            'is_published' => false,
        ]);
        $this->assertSame(Budget::generateCodeForId($budget->id), $budget->code);
    }

    public function test_budget_code_is_generated_automatically_even_if_a_code_is_sent(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
        ]);

        $this->actingAs($user)
            ->post(route('budgets.store'), [
                'code' => 'MANUAL-001',
                'title' => 'Automatic Code Budget',
                'description' => 'The code should be ignored.',
                'budget_date' => '2026-03-08',
                'status' => Budget::STATUS_DRAFT,
            ])
            ->assertRedirect(route('budgets.index'));

        $budget = Budget::query()
            ->where('user_id', $user->id)
            ->where('title', 'Automatic Code Budget')
            ->firstOrFail();

        $this->assertSame(Budget::generateCodeForId($budget->id), $budget->code);
        $this->assertNotSame('MANUAL-001', $budget->code);
    }

    public function test_an_admin_can_view_all_budgets(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);
        $ownerOne = User::factory()->create([
            'role' => User::ROLE_USER,
        ]);
        $ownerTwo = User::factory()->create([
            'role' => User::ROLE_USER,
        ]);

        Budget::query()->create([
            'user_id' => $ownerOne->id,
            'code' => 'BGT-101',
            'title' => 'Budget One',
            'description' => null,
            'budget_date' => '2026-03-08',
            'status' => Budget::STATUS_DRAFT,
            'is_published' => false,
            'total_cost' => 0,
        ]);
        Budget::query()->create([
            'user_id' => $ownerTwo->id,
            'code' => 'BGT-102',
            'title' => 'Budget Two',
            'description' => null,
            'budget_date' => '2026-03-09',
            'status' => Budget::STATUS_PUBLISHED,
            'is_published' => true,
            'total_cost' => 0,
        ]);

        $this->actingAs($admin)
            ->get(route('budgets.index'))
            ->assertOk()
            ->assertSee('N.º')
            ->assertSee('Budget One')
            ->assertSee('Budget Two');
    }

    public function test_a_regular_user_only_sees_their_own_budgets(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
        ]);
        $otherUser = User::factory()->create([
            'role' => User::ROLE_USER,
        ]);

        Budget::query()->create([
            'user_id' => $user->id,
            'code' => 'BGT-201',
            'title' => 'My Budget',
            'description' => null,
            'budget_date' => '2026-03-08',
            'status' => Budget::STATUS_DRAFT,
            'is_published' => false,
            'total_cost' => 0,
        ]);
        Budget::query()->create([
            'user_id' => $otherUser->id,
            'code' => 'BGT-202',
            'title' => 'Other Budget',
            'description' => null,
            'budget_date' => '2026-03-09',
            'status' => Budget::STATUS_DRAFT,
            'is_published' => false,
            'total_cost' => 0,
        ]);

        $this->actingAs($user)
            ->get(route('budgets.index'))
            ->assertOk()
            ->assertSee('N.º')
            ->assertSee('My Budget')
            ->assertSee('Agregar ítem')
            ->assertSee(route('budgets.items.create', $user->budgets()->first()), false)
            ->assertDontSee('Other Budget');
    }

    public function test_a_regular_user_cannot_edit_or_delete_someone_elses_budget(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
        ]);
        $otherUser = User::factory()->create([
            'role' => User::ROLE_USER,
        ]);
        $budget = Budget::query()->create([
            'user_id' => $otherUser->id,
            'code' => 'BGT-301',
            'title' => 'Restricted Budget',
            'description' => null,
            'budget_date' => '2026-03-08',
            'status' => Budget::STATUS_DRAFT,
            'is_published' => false,
            'total_cost' => 0,
        ]);

        $this->actingAs($user)
            ->patch(route('budgets.update', $budget), [
                'code' => 'BGT-301',
                'title' => 'Updated Budget',
                'description' => 'Attempted change',
                'budget_date' => '2026-03-10',
                'status' => Budget::STATUS_PUBLISHED,
            ])
            ->assertForbidden();

        $this->actingAs($user)
            ->delete(route('budgets.destroy', $budget))
            ->assertForbidden();

        $this->assertDatabaseHas('budgets', [
            'id' => $budget->id,
            'title' => 'Restricted Budget',
        ]);
    }

    public function test_a_regular_user_cannot_view_someone_elses_budget_detail(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
        ]);
        $otherUser = User::factory()->create([
            'role' => User::ROLE_USER,
        ]);
        $budget = Budget::query()->create([
            'user_id' => $otherUser->id,
            'code' => 'BGT-302',
            'title' => 'Private Budget',
            'description' => null,
            'budget_date' => '2026-03-08',
            'status' => Budget::STATUS_DRAFT,
            'is_published' => false,
            'total_cost' => 0,
        ]);

        $this->actingAs($user)
            ->get(route('budgets.show', $budget))
            ->assertForbidden();
    }

    public function test_budget_validation_fails_when_required_fields_are_missing(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('budgets.create'))
            ->post(route('budgets.store'), [
                'title' => '',
                'description' => 'Missing required fields',
                'budget_date' => '',
                'status' => '',
            ]);

        $response
            ->assertRedirect(route('budgets.create'))
            ->assertSessionHasErrors(['title', 'budget_date', 'status']);
    }
}
