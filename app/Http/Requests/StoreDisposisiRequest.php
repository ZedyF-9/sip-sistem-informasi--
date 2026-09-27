<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDisposisiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'kepala_sekolah'
            && $this->user()->is_active;
    }

    public function rules(): array
    {
        return [
            'surat_masuk_id' => [
                'required',
                'integer',
                'exists:surat_masuks,id',
            ],
            'penerima_ids' => ['required', 'array', 'min:1'],
            'penerima_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('users', 'id')->where(
                    fn ($query) => $query
                        ->whereIn('role', ['penerima_disposisi', 'waka_humas'])
                        ->where('is_active', true)
                ),
            ],
            'instruksi' => ['required', 'string', 'max:5000'],
            'prioritas' => [
                'required',
                Rule::in(['biasa', 'segera', 'sangat_segera']),
            ],
            'batas_waktu' => [
                'nullable',
                'date',
                'after_or_equal:today',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'penerima_ids.required' => 'Pilih minimal satu penerima.',
            'penerima_ids.*.exists' => 'Penerima tidak aktif atau tidak sesuai.',
            'instruksi.required' => 'Instruksi wajib diisi.',
            'batas_waktu.after_or_equal' =>
                'Batas waktu tidak boleh sebelum hari ini.',
        ];
    }
}
