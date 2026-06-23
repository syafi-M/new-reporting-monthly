<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImageRateStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'image_path_rate' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp'],
            'rate' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:255'],
            'n' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.nullable' => 'Nama tidak wajib diisi.',
            'email.nullable' => 'Email tidak wajib diisi.',
            'rate.required' => 'Rating wajib dipilih.',
            'rate.integer' => 'Rating harus berupa angka.',
            'rate.min' => 'Rating minimal 1.',
            'rate.max' => 'Rating maksimal 5.',
            'n.required' => 'Target area rating tidak ditemukan.',
        ];
    }
}
