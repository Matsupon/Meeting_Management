<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id'             => 'nullable|string|max:255',
            'title'          => 'required|string|max:255',
            'date'           => 'required|date',
            'end_date'       => 'nullable|date|after_or_equal:date',
            'location'       => 'nullable|string|max:255',
            'classification' => 'nullable|string|max:255',
            'description'    => 'nullable|string|max:65535',
            'status'         => 'nullable|string|in:upcoming,completed,cancelled',
            'color'          => 'nullable|string|max:50',
            'day_overrides'  => 'nullable|array',
            'recurrence_rule' => 'nullable|string|max:255',
            'recurrence_end' => 'nullable|date',
        ];
    }
}
