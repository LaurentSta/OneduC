<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGroupeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
  return [
    'nom' => ['required','string','max:150'],
    'description' => ['nullable','string','max:2000'],
    'password' => ['required','string','min:8'],
    'stagiaires' => ['nullable','array'],
    'stagiaires.*.prenom' => ['nullable','string','max:80'],
    'stagiaires.*.nom' => ['nullable','string','max:80'],
    'stagiaires.*.email' => ['nullable','email','max:190'],
    'modules' => ['nullable','array'],
    'modules.*' => ['integer','exists:modules,id'],
  ];
}

}
