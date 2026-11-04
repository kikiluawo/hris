<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class EmailVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! hash_equals((string) $this->user()->getKey(), (string) $this->route('id'))) {
            return false;
        }

        return hash_equals(sha1($this->user()->getEmailForVerification()), (string) $this->route('hash'));
    }

    public function rules(): array
    {
        return [];
    }
}
