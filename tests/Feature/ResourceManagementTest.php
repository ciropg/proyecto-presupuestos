<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Resource;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_admin_can_access_the_resources_module(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)->get(route('admin.resources.index'))->assertOk();
    }

    public function test_a_regular_user_cannot_access_the_resources_module(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
        ]);

        $this->actingAs($user)->get(route('admin.resources.index'))->assertForbidden();
    }

    public function test_an_admin_can_create_a_valid_resource(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);
        $category = Category::query()->create([
            'name' => 'Materials',
            'description' => 'Construction materials',
        ]);
        $unit = Unit::query()->create([
            'name' => 'Kilogram',
            'symbol' => 'kg',
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.resources.store'), [
                'name' => 'Cement',
                'description' => 'Portland cement',
                'category_id' => $category->id,
                'unit_id' => $unit->id,
                'unit_price' => 29.90,
            ]);

        $response->assertRedirect(route('admin.resources.index'));
        $this->assertDatabaseHas('resources', [
            'name' => 'Cement',
            'description' => 'Portland cement',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
        ]);
        $this->assertSame('29.90', Resource::query()->firstOrFail()->unit_price);
    }

    public function test_resource_validation_fails_when_required_fields_are_missing(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this
            ->actingAs($admin)
            ->from(route('admin.resources.create'))
            ->post(route('admin.resources.store'), [
                'name' => '',
                'description' => 'Missing required fields',
                'category_id' => '',
                'unit_id' => '',
                'unit_price' => '',
            ]);

        $response
            ->assertRedirect(route('admin.resources.create'))
            ->assertSessionHasErrors(['name', 'category_id', 'unit_id', 'unit_price']);
    }

    public function test_resource_validation_fails_when_category_or_unit_do_not_exist(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this
            ->actingAs($admin)
            ->from(route('admin.resources.create'))
            ->post(route('admin.resources.store'), [
                'name' => 'Concrete Mixer',
                'description' => 'Equipment rental',
                'category_id' => 999,
                'unit_id' => 999,
                'unit_price' => 150.00,
            ]);

        $response
            ->assertRedirect(route('admin.resources.create'))
            ->assertSessionHasErrors(['category_id', 'unit_id']);
    }
}
