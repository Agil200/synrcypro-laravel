<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IfutsTicketInputRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'category' => $this->upper($this->input('category')),
            'nrp' => trim((string) $this->input('nrp')),
            'name' => trim((string) $this->input('name')),
            'position' => trim((string) $this->input('position')),
            'locality_status' => $this->upper($this->input('locality_status')),
            'phone' => trim((string) $this->input('phone')),
            'nik_ktp' => trim((string) $this->input('nik_ktp')),
            'route_out' => $this->upper($this->input('route_out')),
            'ticket_type_out' => $this->upper($this->input('ticket_type_out')),
            'location_in' => $this->upper($this->input('location_in')),
            'route_in' => $this->upper($this->input('route_in')),
            'ticket_type_in' => $this->upper($this->input('ticket_type_in')),
            'note' => trim((string) $this->input('note')),
        ]);
    }

    public function rules(): array
    {
        return [
            'category' => ['required', Rule::in(['TIKET', 'TRAVEL', 'NON TIKET'])],
            'nrp' => ['required', 'string', 'max:30'],
            'name' => ['required', 'string', 'max:150'],
            'position' => ['required', 'string', 'max:120'],
            'locality_status' => ['required', Rule::in(['LOKAL', 'NON LOKAL'])],
            'phone' => ['required', 'string', 'max:30'],
            'nik_ktp' => ['required', 'string', 'max:30'],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'out_date' => ['required', 'date'],
            'route_out' => ['required', 'string', 'max:120'],
            'ticket_type_out' => ['required', Rule::in(['REGULER', 'TAMBAHAN'])],
            'location_in' => ['required', Rule::in(['PALEMBANG', 'SITE'])],
            'in_date' => ['required', 'date', 'after_or_equal:out_date'],
            'route_in' => ['required', 'string', 'max:120'],
            'ticket_type_in' => ['required', Rule::in(['REGULER', 'TAMBAHAN'])],
            'note' => ['nullable', 'string', 'max:1500'],
        ];
    }

    public function messages(): array
    {
        return [
            'category.required' => 'Kategori wajib dipilih.',
            'category.in' => 'Kategori harus TIKET, TRAVEL, atau NON TIKET.',
            'nrp.required' => 'NRP wajib diisi.',
            'name.required' => 'Nama wajib diisi.',
            'position.required' => 'Jabatan wajib diisi.',
            'locality_status.required' => 'Lokal / Non Lokal wajib dipilih.',
            'phone.required' => 'No HP aktif wajib diisi.',
            'nik_ktp.required' => 'NIK KTP wajib diisi.',
            'birth_date.required' => 'Tanggal lahir wajib diisi.',
            'birth_date.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
            'out_date.required' => 'Tanggal OUT wajib diisi.',
            'route_out.required' => 'Rute OUT wajib diisi.',
            'ticket_type_out.required' => 'Ket Tiket OUT wajib dipilih.',
            'ticket_type_out.in' => 'Ket Tiket OUT harus REGULER atau TAMBAHAN.',
            'location_in.required' => 'Lokasi IN wajib dipilih.',
            'location_in.in' => 'Lokasi IN harus PALEMBANG atau SITE.',
            'in_date.required' => 'Tanggal IN wajib diisi.',
            'in_date.after_or_equal' => 'Tanggal IN tidak boleh lebih awal dari Tanggal OUT.',
            'route_in.required' => 'Rute IN wajib diisi.',
            'ticket_type_in.required' => 'Ket Tiket IN wajib dipilih.',
            'ticket_type_in.in' => 'Ket Tiket IN harus REGULER atau TAMBAHAN.',
            'note.max' => 'Note maksimal 1.500 karakter.',
        ];
    }

    private function upper(mixed $value): string
    {
        return mb_strtoupper(trim((string) $value));
    }
}