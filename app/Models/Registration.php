<?php

namespace App\Models;

use Database\Factories\RegistrationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['full_name', 'whatsapp_number', 'gn_division'])]
class Registration extends Model
{
    /** @use HasFactory<RegistrationFactory> */
    use HasFactory;

    /**
     * Convert the many ways people type a Sri Lankan mobile number
     * (077 123 4567, 771234567, +94 77 123 4567, 0094771234567) into +94771234567.
     *
     * Input that cannot be recognised is returned unchanged so validation can reject it.
     */
    public static function normalizeWhatsappNumber(string $number): string
    {
        $digits = preg_replace('/\D+/', '', $number);

        if (str_starts_with($digits, '0094')) {
            $digits = substr($digits, 2);
        }

        return match (true) {
            strlen($digits) === 11 && str_starts_with($digits, '94') => '+'.$digits,
            strlen($digits) === 10 && str_starts_with($digits, '0') => '+94'.substr($digits, 1),
            strlen($digits) === 9 && str_starts_with($digits, '7') => '+94'.$digits,
            default => trim($number),
        };
    }

    /**
     * Search registrations by name, WhatsApp number, or GN division.
     *
     * @param  Builder<Registration>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        if ($term === '') {
            return;
        }

        $digits = preg_replace('/\D+/', '', $term);
        $numberFragment = str_starts_with($digits, '0') ? substr($digits, 1) : $digits;

        $query->where(function (Builder $query) use ($term, $numberFragment): void {
            $query->where('full_name', 'like', "%{$term}%")
                ->orWhere('gn_division', 'like', "%{$term}%");

            if (strlen($numberFragment) >= 3) {
                $query->orWhere('whatsapp_number', 'like', "%{$numberFragment}%");
            }
        });
    }

    /**
     * A short, human-friendly reference such as SYU-0042.
     *
     * @return Attribute<string, never>
     */
    protected function reference(): Attribute
    {
        return Attribute::get(fn (): string => 'SYU-'.str_pad((string) $this->id, 4, '0', STR_PAD_LEFT));
    }

    /**
     * The number in local format, e.g. 077 123 4567.
     *
     * @return Attribute<string, never>
     */
    protected function localWhatsappNumber(): Attribute
    {
        return Attribute::get(function (): string {
            if (! preg_match('/^\+94(\d{2})(\d{3})(\d{4})$/', $this->whatsapp_number, $parts)) {
                return $this->whatsapp_number;
            }

            return "0{$parts[1]} {$parts[2]} {$parts[3]}";
        });
    }

    /**
     * A wa.me link that opens a WhatsApp chat with this person.
     *
     * @return Attribute<string, never>
     */
    protected function whatsappLink(): Attribute
    {
        return Attribute::get(fn (): string => 'https://wa.me/'.ltrim($this->whatsapp_number, '+'));
    }
}
