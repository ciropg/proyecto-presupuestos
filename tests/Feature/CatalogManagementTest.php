<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_admin_can_access_categories_and_units(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)->get(route('admin.categories.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.units.index'))->assertOk();
    }

    public function test_a_regular_user_cannot_access_categories_or_units(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
        ]);

        $this->actingAs($user)->get(route('admin.categories.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.units.index'))->assertForbidden();
    }

    public function test_an_admin_can_create_a_valid_category(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Materials',
                'description' => 'Construction materials',
            ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Materials',
            'description' => 'Construction materials',
        ]);
    }

    public function test_an_admin_can_create_a_valid_unit(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.units.store'), [
                'name' => 'Kilogram',
                'symbol' => 'kg',
            ]);

        $response->assertRedirect(route('admin.units.index'));
        $this->assertDatabaseHas('units', [
            'name' => 'Kilogram',
            'symbol' => 'kg',
        ]);
    }

    public function test_category_validation_fails_when_required_fields_are_missing(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this
            ->actingAs($admin)
            ->from(route('admin.categories.create'))
            ->post(route('admin.categories.store'), [
                'name' => '',
                'description' => 'Missing name',
            ]);

        $response
            ->assertRedirect(route('admin.categories.create'))
            ->assertSessionHasErrors('name');
    }

    public function test_unit_validation_fails_when_required_fields_are_missing(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this
            ->actingAs($admin)
            ->from(route('admin.units.create'))
            ->post(route('admin.units.store'), [
                'name' => '',
                'symbol' => '',
            ]);

        $response
            ->assertRedirect(route('admin.units.create'))
            ->assertSessionHasErrors(['name', 'symbol']);
    }
}
