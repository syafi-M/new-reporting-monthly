<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UImageUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "user_id" => 'required',
            "clients_id" => 'required',
            "img_before" => 'required',
            "img_proccess" => 'nullable',
            "img_final" => 'required',
            "note" => 'required',
            "max_data" => 'nullable',
        ];
    }
}
