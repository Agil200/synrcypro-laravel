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
            'atr_record_id' => [
                'required',
                'integer',
                'exists:atr_records,id',
            ],

            'coaching_date' => [
                'required',
                'date',
            ],

            'shift' => [
                'required',
                Rule::in(['DAY', 'NIGHT', 'NON SHIFT']),
            ],

            'location' => [
                'required',
                'string',
                'max:150',
            ],

            'coaching_time' => [
                'required',
                'date_format:H:i',
            ],

            'material_personal' => [
                'nullable',
                'boolean',
            ],

            'material_family' => [
                'nullable',
                'boolean',
            ],

            'material_work' => [
                'nullable',
                'boolean',
            ],

            'notes' => [
                'required',
                'string',
                'min:10',
                'max:5000',
            ],

            /*
            |--------------------------------------------------------------------------
            | PIC Roster
            |--------------------------------------------------------------------------
            |
            | Nilai ini hanya untuk kebutuhan tampilan/form.
            | Controller tetap menentukan PIC final berdasarkan POSISI,
            | sehingga nilai dari browser tidak dipercaya sebagai sumber utama.
            |
            */
            'created_by_name' => [
                'nullable',
                'string',
                'max:150',
            ],

            'leader_name' => [
                'required',
                'string',
                'min:3',
                'max:150',
            ],

            /*
            |--------------------------------------------------------------------------
            | Bukti Pemanggilan
            |--------------------------------------------------------------------------
            */
            'evidence' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],

            /*
            |--------------------------------------------------------------------------
            | Signature Pad
            |--------------------------------------------------------------------------
            |
            | Signature dikirim sebagai PNG base64 dari canvas website.
            | Validasi backend tetap wajib agar dokumentasi tidak dapat
            | tersimpan tanpa tanda tangan meskipun JavaScript gagal/dimatikan.
            |
            */
            'creator_signature_data' => [
                'required',
                'string',
                'starts_with:data:image/png;base64,',
                'max:3000000',
            ],

            'leader_signature_data' => [
                'required',
                'string',
                'starts_with:data:image/png;base64,',
                'max:3000000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'atr_record_id.required' =>
                'Data karyawan ATR wajib dipilih.',

            'coaching_date.required' =>
                'Tanggal pemanggilan wajib diisi.',

            'shift.required' =>
                'Shift wajib dipilih.',

            'location.required' =>
                'Lokasi pemanggilan wajib diisi.',

            'coaching_time.required' =>
                'Waktu pemanggilan wajib diisi.',

            'notes.required' =>
                'Keterangan / hasil coaching wajib diisi.',

            'notes.min' =>
                'Keterangan / hasil coaching minimal 10 karakter.',

            'leader_name.required' =>
                'Nama pimpinan wajib diisi.',

            'leader_name.min' =>
                'Nama pimpinan minimal 3 karakter.',

            'evidence.required' =>
                'Bukti pemanggilan wajib dilampirkan.',

            'evidence.mimes' =>
                'Bukti pemanggilan harus berupa JPG, JPEG, PNG, atau PDF.',

            'evidence.max' =>
                'Ukuran bukti pemanggilan maksimal 5 MB.',

            'creator_signature_data.required' =>
                'Tanda tangan karyawan wajib diisi langsung pada website.',

            'creator_signature_data.starts_with' =>
                'Format tanda tangan karyawan tidak valid.',

            'leader_signature_data.required' =>
                'Tanda tangan pimpinan / PIC Roster wajib diisi langsung pada website.',

            'leader_signature_data.starts_with' =>
                'Format tanda tangan pimpinan / PIC Roster tidak valid.',
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
                    'Pilih minimal satu materi coaching: Pribadi, Keluarga, atau Pekerjaan.'
                );
            }
        });
    }
}