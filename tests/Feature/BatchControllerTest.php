<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\Order;
use App\Jobs\ProcessOrder;
use Illuminate\Support\Facades\Bus;
use App\Notifications\BatchCompleted;
use Illuminate\Database\Console\Migrations\RefreshCommand;
use Illuminate\Notifications\Notification;
use Illuminate\Bus\Batch;


use function Pest\Laravel\{actingAs, artisan};
use Illuminate\Support\Facades\Queue;


it('does not allow non-admin users to start a batch', function () {
    $user = User::factory()->create();

    actingAs($user)->post('/batch/start')->assertForbidden();
});

it('allows admin users to start a batch', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'admin']);
    $user->assignRole($role);

    Order::factory()->count(1)->create(['status' => 'pending']);

    actingAs($user)->post('/batch/start')->assertOk();

    expect(Order::where('status', 'pending')->count())->toBe(0);
});

it('updates order status to completed after processing', function () {
    $order = Order::factory()->create(['status' => 'pending']);

    ProcessOrder::dispatchSync($order);

    expect(Order::find($order->id)->status)->toBe('completed');
});



it('fetches the batch status', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'admin']);
    $user->assignRole($role);
    $batch = Bus::batch([])->dispatch();

    actingAs($user)->get("/batch/{$batch->id}")->assertJsonFragment([
        'progress' => 0,
    ]);
});


it('processes a full batch and stores notification', function () {
    // Bus::fake();

    artisan(RefreshCommand::class);

    $user = User::factory()->create();
    $role = Role::create(['name' => 'admin']);
    $user->assignRole($role);

    $orders = Order::factory()->count(3)->create(['status' => 'pending']);

    actingAs($user)->post('/batch/start')->assertOk();

    Bus::assertBatched(function (Batch $batch) use ($orders) {
        return $batch->totalJobs === count($orders);
    });


});
