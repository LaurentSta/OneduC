<?php

namespace App\Http\Requests\Admin\Scorm;

use Illuminate\Foundation\Http\FormRequest;

class ScormImportRequest extends FormRequest
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
            'lecture_id' => ['required', 'integer', 'exists:module_lectures,id'],
            'zip'        => ['required', 'file', 'mimes:zip', 'max:512000'], // 500 Mo
        ];
    }

}
