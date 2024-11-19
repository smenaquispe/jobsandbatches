<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Order;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'user_id' => User::factory(), // Relación con User (se crea un usuario si no existe)
            'status' => $this->faker->randomElement(['pending', 'completed', 'cancelled']),
            'batch_id' => $this->faker->numberBetween(1, 100), // Suponiendo que batch_id sea un número
        ];
    }
}
