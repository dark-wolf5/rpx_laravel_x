<?php

namespace Database\Factories;

use App\Models\DefaultImages;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DefaultImagesFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DefaultImages::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'default_image_url' => null
        ];
        
    }
}
