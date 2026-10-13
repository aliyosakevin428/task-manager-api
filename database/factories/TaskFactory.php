<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),

            'project_id' => function (array $attributes) {
                return Project::factory()->create([
                    'workspace_id' => $attributes['workspace_id'],
                ])->id;
            },

            'title' => $this->faker->sentence(4),
            'description' => $this->faker->optional()->paragraph(),

            'status' => $this->faker->randomElement(['todo', 'doing', 'done']),
            'priority' => $this->faker->numberBetween(1, 3),
            'due_date' => $this->faker->optional()->date(),

            'created_by' => User::factory(),

            'assigned_to' => null,
        ];
    }

    /**
     * Optional helper: task yang assigned ke user tertentu
     */
    public function assignedTo(User $user): static
    {
        return $this->state(fn () => [
            'assigned_to' => $user->id,
        ]);
    }

    /**
     * Optional helper: status tertentu
     */
    public function status(string $status): static
    {
        return $this->state(fn () => [
            'status' => $status,
        ]);
    }
}
