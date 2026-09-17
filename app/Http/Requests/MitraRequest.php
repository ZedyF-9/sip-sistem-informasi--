<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MitraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'koordinator_tu'
            && $this->user()->is_active;
    }

    public function rules(): array
    {
        return [
            'nama_mitra' => ['required', 'string', 'max:255'],
            'bidang_usaha' => ['required', 'string', 'max:150'],
            'alamat' => ['nullable', 'string', 'max:2000'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'deskripsi' => ['required', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'extensions:jpg,jpeg,png,webp',
                'max:2048',
                'dimensions:max_width=4000,max_height=4000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'website.url' => 'Website harus diawali http:// atau https://.',
            'logo.max' => 'Logo maksimal 2 MB.',
            'logo.image' => 'Logo harus berupa gambar.',
            'logo.mimes' => 'Logo hanya JPG, PNG, atau WebP.',
            'logo.dimensions' => 'Dimensi logo maksimal 4000 × 4000 piksel.',
        ];
    }
}   