<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAtrCoachingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'atr_record_id' => ['required', 'integer', 'exists:atr_records,id'],
            'coaching_date' => ['required', 'date'],
            'shift' => ['required', Rule::in(['DAY', 'NIGHT', 'NON SHIFT'])],
            'location' => ['required', 'string', 'max:150'],
            'coaching_time' => ['required', 'date_format:H:i'],
            'material_personal' => ['nullable', 'boolean'],
            'material_family' => ['nullable', 'boolean'],
            'material_work' => ['nullable', 'boolean'],
            'notes' => ['required', 'string', 'min:10', 'max:5000'],
            'created_by_name' => ['required', 'string', 'max:150'],
            'evidence' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'employee_signature' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
            'coach_signature' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $hasMaterial = $this->boolean('material_personal')
                || $this->boolean('material_family')
                || $this->boolean('material_work');

            if (! $hasMaterial) {
                $validator->errors()->add(
                    'material',
                    'Pilih minimal satu materi: Pribadi, Keluarga, atau Pekerjaan.'
                );
            }
        });
    }
}
