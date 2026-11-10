<?php

namespace App\Http\Requests\Cryptography;

use Illuminate\Foundation\Http\FormRequest;

final class CryptographyOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'algorithm' => ['required', 'string'],
            'input' => ['present', 'string'],
            'options' => ['sometimes', 'array'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function options(): array
    {
        // The "array" rule above guarantees the type; this only narrows it.
        /** @var array<string, mixed> $options */
        $options = $this->input('options', []);

        return $options;
    }
}
