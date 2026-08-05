<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
        ];
    }
}
