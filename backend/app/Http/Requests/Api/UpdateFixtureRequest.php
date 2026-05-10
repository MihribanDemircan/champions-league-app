<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFixtureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'homeGoals' => ['required', 'integer', 'min:0', 'max:20'],
            'awayGoals' => ['required', 'integer', 'min:0', 'max:20'],
        ];
    }
}
