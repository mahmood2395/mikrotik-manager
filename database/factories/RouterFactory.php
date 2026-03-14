<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RouterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'        => $this->faker->words(2, true),
            'ip_address'  => $this->faker->localIpv4(),
            'api_port'    => 8728,
            'rest_port'   => 80,
            'username'    => 'admin',
            'password'    => 'password',
            'group'       => null,
            'description' => null,
            'is_active'   => true,
            'last_seen'   => null,
        ];
    }
}