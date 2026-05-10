<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ResetLeagueRequest extends FormRequest
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
            'teams' => ['sometimes', 'nullable', 'array', 'size:4'],
            'teams.*.name' => ['required_with:teams', 'string', 'max:60'],
            'teams.*.power' => ['required_with:teams', 'integer', 'min:1', 'max:100'],
        ];
    }
}
