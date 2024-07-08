<?php

namespace App\Http\Requests;

use App\Enums\Uf;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'uf' => [
                'required',
                'string',
                Rule::in(Uf::cases()),
            ],
            'cnpj' => [
                'required',
                'numeric',
                'digits:14',
                Rule::unique('companies')->ignore($this->company),
            ],
            'contacts' => 'required|array|min:1',
            'contacts.*.name' => 'required|string|max:255',
            'contacts.*.last_name' => 'required|string|max:255',
            'contacts.*.email' => 'required|email|max:255',
            'contacts.*.phone' => 'required|numeric|digits:11',
        ];
    }
}
