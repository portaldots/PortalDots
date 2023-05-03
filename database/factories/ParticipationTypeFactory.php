<?php

namespace Database\Factories;

use App\Eloquents\Form;
use App\Eloquents\ParticipationType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Model>
 */
class ParticipationTypeFactory extends Factory
{
    protected $model = ParticipationType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $usersCountMin = $this->faker->numberBetween(1, 100);
        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->paragraph(),
            'users_count_min' => $usersCountMin,
            'users_count_max' => $this->faker->numberBetween($usersCountMin, 100),
            'form_id' => function () {
                return factory(Form::class)->create()->id;
            }
        ];
    }
}
