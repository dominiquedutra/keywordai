<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Importar Rule

class StoreNegativeKeywordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * For now, let's assume anyone can submit this form.
     * Adjust authorization logic later if needed (e.g., check for authenticated user).
     */
    public function authorize(): bool
    {
        return true; // Permitir a requisição por enquanto
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'term' => ['required', 'string', 'max:255'], // Termo a ser negativado
            'match_type' => [
                'required', 
                'string', 
                Rule::in(['broad', 'phrase', 'exact']) // Deve ser um dos tipos válidos
            ],
            'list_id' => ['required', 'numeric'], // ID da lista (deve ser numérico)
            'reason' => ['nullable', 'string', 'max:1000'], // Razão para adicionar a palavra-chave negativa (opcional)
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'term' => 'termo',
            'match_type' => 'tipo de correspondência',
            'list_id' => 'ID da lista',
            'reason' => 'motivo',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'term.required' => 'O termo é obrigatório.',
            'term.max' => 'O termo não pode ter mais de :max caracteres.',
            'match_type.required' => 'O tipo de correspondência é obrigatório.',
            'match_type.in' => 'O tipo de correspondência deve ser broad, phrase ou exact.',
            'list_id.required' => 'O ID da lista é obrigatório.',
            'list_id.numeric' => 'O ID da lista deve ser um número.',
            'reason.max' => 'O motivo não pode ter mais de :max caracteres.',
        ];
    }
}
