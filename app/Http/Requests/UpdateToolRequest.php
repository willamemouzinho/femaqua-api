<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateToolRequest extends FormRequest
{
    public function authorize() : bool
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'title' => ['required', 'string'],
            'link' => ['required', 'url'],
            'description' => ['required', 'string'],
            'tags' => ['required', 'array']
        ];
    }
}
