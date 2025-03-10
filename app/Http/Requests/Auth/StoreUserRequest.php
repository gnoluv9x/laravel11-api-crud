<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreUserRequest extends FormRequest
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
            'name' => [
                'required',
                'min:3',
                'max:255'
            ],
            'email' => [
                'required',
                'email',
                'unique:App\Models\User,email'
            ],
            'password' => [
                'required',
                'min:6',
                'confirmed:password_confirm'
            ],
            'password_confirm' => [
                'required',
                'min:6'
            ],
            'birthdate' => [
                'nullable',
                Rule::date()->format('d-m-Y'),
            ],
            'phone' => [
                'nullable',
                'regex:/\+?\d{1,4}?[-.\s]?\(?\d{1}?\)?[-.\s]?\d{1,3}[-.\s]?\d{1,3}[-.\s]?\d{1,3}/'
            ],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();

        throw new HttpResponseException(
            response()->json(['errors' => $errors], 400)
        );
    }
}
