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

    public function test_a_user_can_add_a_manual_item_without_a_catalog_resource(): void
    {
        $user = $this->createUser();
        $budget = $this->createBudget($user, 'BGT-ITEM-002');
        $unit = Unit::query()->create([
            'name' => 'Hour',
            'symbol' => 'hr',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('budgets.items.store', $budget), [
                'resource_id' => null,
                'name' => 'Electrical installation',
                'unit_id' => $unit->id,
                'description' => 'Manual service item',
                'quantity' => 5,
                'unit_price' => 18.75,
            ]);

        $response->assertRedirect(route('budgets.show', $budget));
        $this->assertDatabaseHas('budget_items', [
            'budget_id' => $budget->id,
            'resource_id' => null,
            'unit_id' => $unit->id,
            'name' => 'Electrical installation',
            'description' => 'Manual service item',
        ]);

        $budgetItem = BudgetItem::query()->firstOrFail();

        $this->assertSame('93.75', $budgetItem->subtotal);
        $this->assertSame('93.75', $budget->fresh()->total_cost);
    }

    public function test_a_user_can_add_a_valid_subitem_to_a_budget_item(): void
    {
        $user = $this->createUser();
        $budget = $this->createBudget($user, 'BGT-SUB-001');
        $unit = Unit::query()->create([
            'name' => 'Lot',
            'symbol' => 'lot',
        ]);
        $parentItem = $this->createBudgetItem($budget, [
            'unit_id' => $unit->id,
            'name' => 'Groundworks',
            'description' => 'Grouping item',
            'quantity' => 1,
            'unit_price' => 0,
            'subtotal' => 0,
            'sort_order' => 1,
        ]);
        $resource = $this->createResource('Excavation', 'hr', 'Workforce', 50.00);

        $response = $this
            ->actingAs($user)
            ->post(route('budgets.items.store', $budget), [
                'parent_id' => $parentItem->id,
                'resource_id' => $resource->id,
                'description' => 'Machine work',
                'quantity' => 2,
                'unit_price' => 50.00,
            ]);

        $response->assertRedirect(route('budgets.show', $budget));
        $this->assertDatabaseHas('budget_items', [
            'budget_id' => $budget->id,
            'parent_id' => $parentItem->id,
            'resource_id' => $resource->id,
            'name' => 'Excavation',
        ]);

        $parentItem->refresh();

        $this->assertSame('100.00', $parentItem->subtotal);
        $this->assertSame('100.00', $budget->fresh()->total_cost);
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

    public function test_budget_item_validation_fails_when_parent_does_not_exist(): void
    {
        $user = $this->createUser('missing-parent@example.com');
        $budget = $this->createBudget($user, 'BGT-PARENT-404');
        $unit = Unit::query()->create([
            'name' => 'Meter',
            'symbol' => 'm',
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('budgets.items.create', $budget))
            ->post(route('budgets.items.store', $budget), [
                'parent_id' => 999,
                'name' => 'Child item',
                'unit_id' => $unit->id,
                'quantity' => 3,
                'unit_price' => 12.50,
            ]);

        $response
            ->assertRedirect(route('budgets.items.create', $budget))
            ->assertSessionHasErrors(['parent_id']);
    }

    public function test_budget_item_validation_fails_when_parent_belongs_to_another_budget(): void
    {
        $user = $this->createUser('cross-parent@example.com');
        $budget = $this->createBudget($user, 'BGT-PARENT-001');
        $otherBudget = $this->createBudget($user, 'BGT-PARENT-002');
        $unit = Unit::query()->create([
            'name' => 'Piece',
            'symbol' => 'pc',
        ]);
        $foreignParent = $this->createBudgetItem($otherBudget, [
            'unit_id' => $unit->id,
            'name' => 'Foreign parent',
            'quantity' => 1,
            'unit_price' => 10,
            'subtotal' => 10,
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('budgets.items.create', $budget))
            ->post(route('budgets.items.store', $budget), [
                'parent_id' => $foreignParent->id,
                'name' => 'Invalid child',
                'unit_id' => $unit->id,
                'quantity' => 2,
                'unit_price' => 5,
            ]);

        $response
            ->assertRedirect(route('budgets.items.create', $budget))
            ->assertSessionHasErrors(['parent_id']);
    }

    public function test_a_budget_item_cannot_be_its_own_parent(): void
    {
        $user = $this->createUser('self-parent@example.com');
        $budget = $this->createBudget($user, 'BGT-SELF-001');
        $unit = Unit::query()->create([
            'name' => 'Hour',
            'symbol' => 'hr',
        ]);
        $budgetItem = $this->createBudgetItem($budget, [
            'unit_id' => $unit->id,
            'name' => 'Self parent item',
            'quantity' => 1,
            'unit_price' => 30,
            'subtotal' => 30,
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('budgets.items.edit', [$budget, $budgetItem]))
            ->put(route('budgets.items.update', [$budget, $budgetItem]), [
                'parent_id' => $budgetItem->id,
                'name' => 'Self parent item',
                'unit_id' => $unit->id,
                'quantity' => 1,
                'unit_price' => 30,
            ]);

        $response
            ->assertRedirect(route('budgets.items.edit', [$budget, $budgetItem]))
            ->assertSessionHasErrors(['parent_id']);
    }

    public function test_a_budget_item_cannot_be_assigned_to_one_of_its_descendants(): void
    {
        $user = $this->createUser('cycle@example.com');
        $budget = $this->createBudget($user, 'BGT-CYCLE-001');
        $unit = Unit::query()->create([
            'name' => 'Package',
            'symbol' => 'pkg',
        ]);
        $parentItem = $this->createBudgetItem($budget, [
            'unit_id' => $unit->id,
            'name' => 'Parent item',
            'quantity' => 1,
            'unit_price' => 0,
            'subtotal' => 0,
        ]);
        $childItem = $this->createBudgetItem($budget, [
            'parent_id' => $parentItem->id,
            'unit_id' => $unit->id,
            'name' => 'Child item',
            'quantity' => 2,
            'unit_price' => 8,
            'subtotal' => 16,
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('budgets.items.edit', [$budget, $parentItem]))
            ->put(route('budgets.items.update', [$budget, $parentItem]), [
                'parent_id' => $childItem->id,
                'name' => 'Parent item',
                'unit_id' => $unit->id,
                'quantity' => 1,
                'unit_price' => 0,
            ]);

        $response
            ->assertRedirect(route('budgets.items.edit', [$budget, $parentItem]))
            ->assertSessionHasErrors(['parent_id']);
    }

    public function test_budget_detail_lists_hierarchical_items_with_expand_controls(): void
    {
        $user = $this->createUser('tree-view@example.com');
        $budget = $this->createBudget($user, 'BGT-TREE-001');
        $unit = Unit::query()->create([
            'name' => 'Service',
            'symbol' => 'svc',
        ]);
        $parentItem = $this->createBudgetItem($budget, [
            'unit_id' => $unit->id,
            'name' => 'Parent item',
            'description' => 'Parent description',
            'quantity' => 1,
            'unit_price' => 0,
            'subtotal' => 0,
        ]);
        $childItem = $this->createBudgetItem($budget, [
            'parent_id' => $parentItem->id,
            'unit_id' => $unit->id,
            'name' => 'Child item',
            'description' => 'Child description',
            'quantity' => 2,
            'unit_price' => 15,
            'subtotal' => 30,
        ]);

        $budget->recalculateTotalCost();

        $this->actingAs($user)
            ->get(route('budgets.show', $budget))
            ->assertOk()
            ->assertSee($parentItem->name)
            ->assertSee($childItem->name)
            ->assertSee('Add Subitem')
            ->assertSee('Toggle subitems');
    }

    public function test_parent_subtotals_and_budget_totals_are_recalculated_from_children(): void
    {
        $user = $this->createUser('recalc@example.com');
        $budget = $this->createBudget($user, 'BGT-TOTAL-001');
        $unit = Unit::query()->create([
            'name' => 'Task',
            'symbol' => 'tsk',
        ]);
        $parentItem = $this->createBudgetItem($budget, [
            'unit_id' => $unit->id,
            'name' => 'Parent item',
            'quantity' => 1,
            'unit_price' => 0,
            'subtotal' => 0,
        ]);
        $this->createBudgetItem($budget, [
            'parent_id' => $parentItem->id,
            'unit_id' => $unit->id,
            'name' => 'Child A',
            'quantity' => 2,
            'unit_price' => 10,
            'subtotal' => 20,
        ]);
        $this->createBudgetItem($budget, [
            'parent_id' => $parentItem->id,
            'unit_id' => $unit->id,
            'name' => 'Child B',
            'quantity' => 1,
            'unit_price' => 15,
            'subtotal' => 15,
        ]);
        $leafItem = $this->createBudgetItem($budget, [
            'unit_id' => $unit->id,
            'name' => 'Leaf item',
            'quantity' => 1,
            'unit_price' => 5,
            'subtotal' => 5,
        ]);

        $budget->recalculateTotalCost();
        $parentItem->refresh();
        $leafItem->refresh();

        $this->assertSame('35.00', $parentItem->subtotal);
        $this->assertSame('5.00', $leafItem->subtotal);
        $this->assertSame('40.00', $budget->fresh()->total_cost);
    }

    public function test_an_admin_can_manage_items_of_another_users_budget(): void
    {
        $owner = $this->createUser('owner-admin-test@example.com');
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'role' => User::ROLE_ADMIN,
        ]);
        $budget = $this->createBudget($owner, 'BGT-ADMIN-001');
        $unit = Unit::query()->create([
            'name' => 'Visit',
            'symbol' => 'vst',
        ]);

        $this->actingAs($admin)
            ->post(route('budgets.items.store', $budget), [
                'name' => 'Admin item',
                'unit_id' => $unit->id,
                'quantity' => 2,
                'unit_price' => 22.50,
            ])
            ->assertRedirect(route('budgets.show', $budget));

        $this->assertDatabaseHas('budget_items', [
            'budget_id' => $budget->id,
            'name' => 'Admin item',
        ]);
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

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createBudgetItem(Budget $budget, array $attributes): BudgetItem
    {
        return BudgetItem::query()->create(array_merge([
            'budget_id' => $budget->id,
            'parent_id' => null,
            'resource_id' => null,
            'unit_id' => null,
            'name' => 'Budget item',
            'description' => null,
            'quantity' => 1,
            'unit_price' => 0,
            'subtotal' => 0,
            'sort_order' => null,
        ], $attributes));
    }
}
