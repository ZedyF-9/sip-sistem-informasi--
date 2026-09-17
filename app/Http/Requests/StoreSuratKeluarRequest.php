<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSuratKeluarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'koordinator_tu'
            && $this->user()->is_active;
    }

    public function rules(): array
    {
        $editing = $this->route('suratKeluar') !== null;

        return [
            'perihal' => ['required', 'string', 'max:255'],
            'tujuan' => ['required', 'string', 'max:255'],
            'jenis_surat' => ['required', 'string', 'max:100'],
            'sifat_surat' => [
                'required',
                Rule::in(['biasa', 'penting', 'rahasia']),
            ],
            'tanggal_surat' => ['required', 'date'],
            'file_surat' => [
                $editing ? 'nullable' : 'required',
                'file',
                'mimes:pdf',
                'extensions:pdf',
                'max:5120',
            ],
            'version' => [
                $editing ? 'required' : 'nullable',
                'integer',
                'min:1',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'file_surat.max' => 'PDF maksimal 5 MB.',
            'file_surat.mimes' => 'Berkas harus PDF.',
            'file_surat.extensions' => 'Ekstensi berkas harus .pdf.',
        ];
    }
}