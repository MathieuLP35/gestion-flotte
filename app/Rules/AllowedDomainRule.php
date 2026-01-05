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
        // 1. Extraction du domaine
        $domain = substr(strrchr($value, "@"), 1);

        // 2. Vérification en base
        $isAllowed = AllowedDomain::where('name', $domain)->exists();

        if (!$isAllowed) {
            // Utilisation du helper __() pour injecter la variable proprement
            $fail(__("Désolé, le domaine :domain n'est pas autorisé.", ['domain' => $domain]));
        }
    }
}