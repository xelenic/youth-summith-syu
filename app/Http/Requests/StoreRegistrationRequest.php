<?php

namespace App\Http\Requests;

use App\Models\Registration;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreRegistrationRequest extends FormRequest
{
    /**
     * Registration is open to everyone.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'min:2', 'max:120'],
            'whatsapp_number' => ['required', 'string', 'regex:/^\+947\d{8}$/', 'unique:registrations,whatsapp_number'],
            'gn_division' => ['required', 'string', 'max:100'],
            'terms' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'Please enter your full name.',
            'whatsapp_number.required' => 'Please enter your WhatsApp number.',
            'whatsapp_number.regex' => 'Enter a valid Sri Lankan mobile number, e.g. 077 123 4567.',
            'whatsapp_number.unique' => 'This WhatsApp number is already registered.',
            'gn_division.required' => 'Please enter your GN Division.',
            'terms.accepted' => 'Please agree to the terms and conditions.',
        ];
    }

    /**
     * Tidy the input before validating it.
     */
    protected function prepareForValidation(): void
    {
        $fullName = $this->input('full_name');
        $whatsappNumber = $this->input('whatsapp_number');
        $gnDivision = $this->input('gn_division');

        $this->merge([
            'full_name' => is_string($fullName) ? Str::squish($fullName) : $fullName,
            'whatsapp_number' => is_string($whatsappNumber) ? Registration::normalizeWhatsappNumber($whatsappNumber) : $whatsappNumber,
            'gn_division' => is_string($gnDivision) ? $this->canonicalGnDivision(Str::squish($gnDivision)) : $gnDivision,
        ]);
    }

    /**
     * Reuse the existing spelling when someone types a known division in a different case,
     * so "buttala" and "Buttala" are counted together.
     */
    private function canonicalGnDivision(string $name): string
    {
        if ($name === '') {
            return $name;
        }

        $lowercaseName = mb_strtolower($name);

        $configured = collect(config('summit.gn_divisions'))
            ->first(fn (string $division): bool => mb_strtolower($division) === $lowercaseName);

        return $configured
            ?? Registration::query()->whereRaw('lower(gn_division) = ?', [$lowercaseName])->value('gn_division')
            ?? $name;
    }
}
