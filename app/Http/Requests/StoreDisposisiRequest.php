<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDisposisiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'koordinator_tu'
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
            'catatan_pengajuan' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'catatan_pengajuan.max' => 'Catatan pengajuan maksimal 2.000 karakter.',
        ];
    }
}
