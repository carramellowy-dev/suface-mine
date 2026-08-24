<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUtilizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
             'unit_id' => 'required|exists:units,id',
            'status' => 'required|in:breakdown,servis,ready',
            'started_at' => 'required_if:status,breakdown,servis|nullable|date',
            'ended_at' => 'nullable|date|after_or_equal:started_at',
            'deskripsi' => 'nullable|string|max:500',
        ];
    }
}
