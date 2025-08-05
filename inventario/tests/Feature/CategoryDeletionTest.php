<?php

namespace Tests\Feature;

use App\Models\{User, Category, Product};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_delete_category_with_children(): void
    {
        $user = User::factory()->create();
        $parent = Category::create(['name' => 'Parent']);
        Category::create(['name' => 'Child', 'parent_id' => $parent->id]);

        $response = $this->actingAs($user)->delete(route('categories.destroy', $parent));

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHasErrors('category');
        $this->assertDatabaseHas('categories', ['id' => $parent->id]);
    }

    public function test_cannot_delete_category_with_products(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Parent']);
        Product::create([
            'name' => 'Prod',
            'sku' => 'P1',
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHasErrors('category');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_can_delete_category_without_relations(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Solo']);

        $response = $this->actingAs($user)->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
