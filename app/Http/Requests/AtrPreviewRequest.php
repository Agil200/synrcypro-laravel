<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AtrPreviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $max = (int) config('atr.upload.max_kilobytes', 10240);

        return [
            'atr_file' => [
                'required',
                'file',
                'mimes:xlsx',
                'max:' . $max,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'atr_file.required' => 'File ATR wajib dipilih.',
            'atr_file.mimes' => 'Format file wajib XLSX.',
            'atr_file.max' => 'Ukuran file ATR melebihi batas yang diizinkan.',
        ];
    }
}
