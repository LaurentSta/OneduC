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

    public function messages(): array
    {
        return [
            'lecture_id.required' => 'La leçon cible est obligatoire.',
            'lecture_id.exists' => 'La leçon sélectionnée est introuvable.',
            'zip.required' => 'Le fichier ZIP SCORM est obligatoire.',
            'zip.file' => 'Le fichier ZIP SCORM est invalide.',
            'zip.uploaded' => 'Le fichier ZIP n\'a pas pu être téléversé. Vérifiez la taille (max 500 Mo).',
            'zip.mimes' => 'Le fichier doit être un ZIP (.zip).',
            'zip.max' => 'Le fichier ZIP ne doit pas dépasser 500 Mo.',
        ];
    }

}
