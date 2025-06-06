<?php

namespace Database\Factories;

use App\Models\RpxUser;
use Illuminate\Database\Eloquent\Factories\Factory;

class RpxUserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RpxUser::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $randomNumber = rand(0, 1025);

        return [
            'first_name'      => $this->faker->firstName,
            'default_picture' => "https://picsum.photos/id/$randomNumber/200/300",
            'last_name'       => $this->faker->lastName,
            'description'     => $this->faker->text(500),
        ];
    }
}
