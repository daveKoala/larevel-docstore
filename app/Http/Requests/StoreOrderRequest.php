<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // If project_guid is missing or invalid, let validation handle it
        if (!$this->input('project_guid')) {
            return true; // Let validation rules catch this
        }

        $project = Project::where('guid', $this->input('project_guid'))->first();

        // If project doesn't exist, let validation handle it
        if (!$project) {
            return true; // Let validation rules catch this
        }

        // Check if user belongs to this project
        return $this->user()->projects()->where('projects.id', $project->id)->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'project_guid' => [
                'required',
                'string',
                Rule::exists('projects', 'guid'),
            ],
            'details' => [
                'required',
                'string',
                'max:5000',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'project_guid.required' => 'A project is required to create an order.',
            'project_guid.exists' => 'The specified project does not exist.',
            'details.required' => 'Order details are required.',
            'details.max' => 'Order details must not exceed 5000 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'project_guid' => 'project',
            'details' => 'order details',
        ];
    }
}
