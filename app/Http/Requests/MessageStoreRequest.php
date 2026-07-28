<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MessageStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => ['nullable', 'string', 'max:2000', 'required_without:image'],
            'image' => ['nullable', 'image', 'max:5120', 'required_without:body'],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required_without' => 'Write a message or attach an image.',
            'image.required_without' => 'Write a message or attach an image.',
        ];
    }
}
