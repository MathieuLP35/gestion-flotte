<?php

namespace App\Rules;

use App\Models\AllowedDomain;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AllowedDomainRule implements ValidationRule
{
    /**
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Extraction du domaine
        $domain = substr(strrchr($value, '@'), 1);

        // Vérification en base
        $isAllowed = AllowedDomain::where('name', $domain)->exists();

        if (! $isAllowed) {
            $fail(__("Désolé, le domaine :domain n'est pas autorisé.", ['domain' => $domain]));
        }
    }
}
