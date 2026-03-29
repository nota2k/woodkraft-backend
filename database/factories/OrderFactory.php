<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_number' => 'ORD-' . strtoupper(fake()->unique()->bothify('??###??')),
            'status' => fake()->randomElement(['pending', 'processing', 'shipped', 'delivered']),
            'total_amount' => 0, // Calculated later
            'shipping_address' => fake()->address(),
            'billing_address' => fake()->address(),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->phoneNumber(),
            'notes' => fake()->sentence(),
        ];
    }

    /**
     * Add random order items and calculate total.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Order $order) {
            $products = Product::inRandomOrder()->limit(rand(1, 4))->get();
            
            if ($products->isEmpty()) {
                // If no products exist, create one to avoid empty orders
                $products = Product::factory(1)->create();
            }

            $total = 0;
            foreach ($products as $prod) {
                $qty = rand(1, 4);
                $unitPrice = $prod->price;
                $lineTotal = $unitPrice * $qty;
                $total += $lineTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $prod->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal,
                ]);
            }

            $order->update(['total_amount' => $total]);
        });
    }
}
