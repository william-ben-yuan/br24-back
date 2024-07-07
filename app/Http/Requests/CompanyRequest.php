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
            'title' => 'required|max:255',
            'email' => 'required|email|max:255',
            'address' => 'required|max:255',
            'city' => 'required|max:255',
            'uf' => [
                'required',
                Rule::in(Uf::cases()),
            ],
            'cnpj' => 'required|max:14',
        ];
    }
}
