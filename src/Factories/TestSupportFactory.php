<?php

namespace Indeev\LaravelRapidDbAnonymizer\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Indeev\LaravelRapidDbAnonymizer\Tests\Support\TestSupport;

class TestSupportFactory extends Factory
{
    protected $model = TestSupport::class;

    public function definition()
    {
        return [
            'randomDigit' => $this->faker->randomDigit,
            'randomNumber' => $this->faker->randomNumber(5, true),
            'randomFloat' => $this->faker->randomFloat(5, 3, 4),
            'numberBetween' => $this->faker->numberBetween(1000, 2000),
            'randomElements' => $this->faker->randomElements(['a', 'b', 1, 2], 2),
            'randomElement' => $this->faker->randomElement(['a', 'b', 1, 2]),
            'shuffle' => $this->faker->shuffle(['a', 'b', 1, 2]),
            'ignoreNull' => null,
            'exactValue' => $this->faker->word,
        ];
    }
}
