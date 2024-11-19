<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Notifications\BatchCompleted;
use function Pest\Laravel\{actingAs};

it('stores batch completed notification in the database', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'admin']);

    $user->assignRole($role);

    $user->notify(new BatchCompleted(10, 2));

    $this->assertDatabaseHas('notifications', [
        'notifiable_id' => $user->id,
        'type' => BatchCompleted::class,
    ]);
});

it('fetches unread notifications', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'admin']);
    $user->assignRole($role);

    $user->notify(new BatchCompleted(10, 2));

    actingAs($user)->get('/notification/unread')->assertJsonFragment([
        'message' => 'El batch ha finalizado.',
        'successfulJobs' => 10,
        'failedJobs' => 2,
    ]);
});
