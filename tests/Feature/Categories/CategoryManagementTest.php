<?php

use App\Models\Category;
use Livewire\Livewire;

beforeEach(function () {
    actAsAdmin($this);
});

it('creates a category', function () {
    Livewire::test('categories.category-management')
        ->set('form.name', 'Electronics')
        ->call('create')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal-create-category');

    $this->assertDatabaseHas('categories', [
        'name' => 'Electronics',
    ]);
});

it('validates category data when creating', function () {
    Livewire::test('categories.category-management')
        ->set('form.name', '')
        ->call('create')
        ->assertHasErrors(['form.name' => 'required']);
});

it('updates a category', function () {
    $category = Category::factory()->create(['name' => 'Old Name']);

    Livewire::test('categories.category-management')
        ->call('openEdit', $category->id)
        ->set('form.name', 'New Name')
        ->call('updateCategory')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal-edit-category');

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'New Name',
    ]);
});

it('allows keeping the same name while updating the same category', function () {
    $category = Category::factory()->create(['name' => 'Stationery']);

    Livewire::test('categories.category-management')
        ->call('openEdit', $category->id)
        ->set('form.name', 'Stationery')
        ->call('updateCategory')
        ->assertHasNoErrors();
});

it('prevents duplicate category names when updating another category', function () {
    $first = Category::factory()->create(['name' => 'Books']);
    $second = Category::factory()->create(['name' => 'Games']);

    Livewire::test('categories.category-management')
        ->call('openEdit', $second->id)
        ->set('form.name', $first->name)
        ->call('updateCategory')
        ->assertHasErrors(['form.name' => 'unique']);
});

it('deletes a category', function () {
    $category = Category::factory()->create();

    Livewire::test('categories.category-management')
        ->call('setDelete', $category->id)
        ->call('delete')
        ->assertDispatched('close-modal-delete-category');

    $this->assertDatabaseMissing('categories', [
        'id' => $category->id,
    ]);
});

it('filters categories by search term', function () {
    Category::factory()->create(['name' => 'Electronics']);
    Category::factory()->create(['name' => 'Furniture']);

    $component = Livewire::test('categories.category-management')
        ->set('search', 'Electro');

    $categories = $component->get('categories');

    $this->assertSame(1, $categories->total());
    $this->assertSame(['Electronics'], collect($categories->items())->pluck('name')->all());
});

it('paginates categories using per page selection', function () {
    Category::factory()->count(15)->create();

    $component = Livewire::test('categories.category-management')
        ->set('perPage', 10);

    $this->assertCount(10, $component->get('categories'));

    $component->call('setPage', 2);

    $this->assertCount(5, $component->get('categories'));
});
