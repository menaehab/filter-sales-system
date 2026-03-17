<?php

use App\Models\Expense;
use Livewire\Livewire;

beforeEach(function () {
    actAsAdmin($this);
});

it('creates an expense', function () {
    Livewire::test('expenses.expense-management')
        ->set('form.amount', 150.50)
        ->set('form.description', 'Office supplies')
        ->call('create')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal-create-expense');

    $this->assertDatabaseHas('expenses', [
        'amount' => '150.50',
        'description' => 'Office supplies',
        'user_id' => auth()->id(),
    ]);
});

it('validates amount is required', function () {
    Livewire::test('expenses.expense-management')
        ->set('form.amount', null)
        ->call('create')
        ->assertHasErrors(['form.amount' => 'required']);
});

it('validates amount is positive', function () {
    Livewire::test('expenses.expense-management')
        ->set('form.amount', -10)
        ->call('create')
        ->assertHasErrors(['form.amount']);

    Livewire::test('expenses.expense-management')
        ->set('form.amount', 0)
        ->call('create')
        ->assertHasErrors(['form.amount']);
});

it('allows description to be optional', function () {
    Livewire::test('expenses.expense-management')
        ->set('form.amount', 100)
        ->set('form.description', '')
        ->call('create')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('expenses', [
        'amount' => '100.00',
    ]);
});

it('updates an expense', function () {
    $expense = Expense::factory()->create([
        'amount' => 100,
        'description' => 'Old description',
        'user_id' => auth()->id(),
    ]);

    Livewire::test('expenses.expense-management')
        ->call('openEdit', $expense->id)
        ->set('form.amount', 200)
        ->set('form.description', 'Updated description')
        ->call('updateExpense')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal-edit-expense');

    $this->assertDatabaseHas('expenses', [
        'id' => $expense->id,
        'amount' => '200.00',
        'description' => 'Updated description',
    ]);
});

it('deletes an expense', function () {
    $expense = Expense::factory()->create([
        'user_id' => auth()->id(),
    ]);

    Livewire::test('expenses.expense-management')
        ->call('setDelete', $expense->id)
        ->call('delete')
        ->assertDispatched('close-modal-delete-expense');

    $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
});

it('calculates total expenses correctly', function () {
    Expense::factory()->create(['amount' => 100, 'user_id' => auth()->id()]);
    Expense::factory()->create(['amount' => 200, 'user_id' => auth()->id()]);
    Expense::factory()->create(['amount' => 50, 'user_id' => auth()->id()]);

    $component = Livewire::test('expenses.expense-management');

    expect($component->get('totalExpenses'))->toBe(350.0);
});

it('filters total expenses by search term', function () {
    Expense::factory()->create(['amount' => 100, 'description' => 'Office supplies', 'user_id' => auth()->id()]);
    Expense::factory()->create(['amount' => 200, 'description' => 'Transportation', 'user_id' => auth()->id()]);

    $component = Livewire::test('expenses.expense-management')
        ->set('search', 'Office');

    expect($component->get('totalExpenses'))->toBe(100.0);
});

it('filters expenses by search term', function () {
    Expense::factory()->create([
        'description' => 'Office supplies purchase',
        'user_id' => auth()->id(),
    ]);

    Expense::factory()->create([
        'description' => 'Transportation costs',
        'user_id' => auth()->id(),
    ]);

    $component = Livewire::test('expenses.expense-management')
        ->set('search', 'Office');

    $expenses = $component->get('expenses');

    $this->assertSame(1, $expenses->total());
});

it('paginates expenses', function () {
    foreach (range(1, 15) as $i) {
        Expense::factory()->create([
            'amount' => $i * 10,
            'user_id' => auth()->id(),
        ]);
    }

    $component = Livewire::test('expenses.expense-management')
        ->set('perPage', 10);

    $this->assertCount(10, $component->get('expenses'));

    $component->call('setPage', 2);

    $this->assertCount(5, $component->get('expenses'));
});
