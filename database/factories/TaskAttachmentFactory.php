<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TaskAttachment>
 */
class TaskAttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $original = $this->faker->words(2, true). '.txt';

        return [
            'task_id' => Task::factory(),
            'user_id' => User::factory(),
            'disk' => 'local',
            'path' => 'attachments/dummy/' .Str::uuid().'-'.$original,
            'original_name' => $original,
            'mime_type' => 'text/plain',
            'size' => 0,
        ];
    }
}
