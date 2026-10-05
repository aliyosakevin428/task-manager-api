<?php

namespace App\Http\Requests\Tasks;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $workspace = $this->route('workspace');

        return [
            'title' => ['required','string','max:255'],
            'description' => ['nullable','string','max:5000'],
            'status' => ['sometimes', Rule::in(['todo','doing','done'])],
            'priority' => ['sometimes','integer','min:1','max:3'],
            'due_date' => ['sometimes','nullable','date'],

            'assigned_to' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('workspace_members', 'user_id')
                    ->where('workspace_id', $workspace->id),
            ]
        ];
    }
}
