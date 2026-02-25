<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKeywordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * We can assume authorization is handled elsewhere (e.g., middleware) for now.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Allow all requests for now
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search_term' => ['required', 'string', 'max:255'],
            'ad_group_id' => ['required', 'integer', 'min:1'],
            'match_type' => [
                'required',
                'string',
                Rule::in(['exact', 'phrase', 'broad']), // Validate against allowed lowercase types
            ],
            // Add ad_group_name just for display purposes, no validation needed here
            'ad_group_name' => ['sometimes', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'search_term.required' => 'O termo de pesquisa é obrigatório.',
            'ad_group_id.required' => 'O ID do grupo de anúncios é obrigatório.',
            'ad_group_id.integer' => 'O ID do grupo de anúncios deve ser um número inteiro.',
            'ad_group_id.min' => 'O ID do grupo de anúncios deve ser um número positivo.',
            'match_type.required' => 'O tipo de correspondência é obrigatório.',
            'match_type.in' => 'O tipo de correspondência selecionado é inválido. Use "exact", "phrase" ou "broad".',
        ];
    }
}
