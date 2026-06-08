<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewExcuseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'decision' => 'required|in:approved,rejected',
            'review_note' => 'nullable|string|max:500',
        ];
    }
}
