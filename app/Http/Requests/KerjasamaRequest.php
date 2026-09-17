<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KerjasamaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'koordinator_tu'
            && $this->user()->is_active;
    }

    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:255'],
            'nomor_mou' => ['nullable', 'string', 'max:100'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => [
                'nullable',
                'date',
                'after_or_equal:tanggal_mulai',
            ],
            'status' => ['required', Rule::in(['aktif', 'berakhir'])],
            'dokumen_mou' => [
                'nullable',
                'file',
                'mimes:pdf',
                'extensions:pdf',
                'max:5120',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'tanggal_selesai.after_or_equal' =>
                'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'dokumen_mou.max' => 'Dokumen MoU maksimal 5 MB.',
            'dokumen_mou.mimes' => 'Dokumen MoU harus PDF.',
        ];
    }
}