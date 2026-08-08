<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AtrCommitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'preview_token' => ['required', 'uuid'],
            'import_action' => [
                'required',
                'string',
                Rule::in(['NEW', 'REPLACE', 'APPEND']),
            ],
            'existing_import_id' => [
                'nullable',
                'integer',
                'exists:atr_imports,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'import_action.required' => 'Pilih tindakan import terlebih dahulu.',
            'import_action.in' => 'Tindakan import ATR tidak valid.',
            'existing_import_id.exists' => 'Snapshot ATR lama tidak ditemukan.',
        ];
    }
}