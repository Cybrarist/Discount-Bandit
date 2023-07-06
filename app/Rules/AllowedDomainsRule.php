<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AllowedDomainsRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
            $allowed_domains=[
                "amazon.ae",
                "amazon.com",
            ];

            $domain_temp=\Str::replace("www." , "", $value);
            $domain=explode("/" , $domain_temp);

            if (!in_array($domain[2], $allowed_domains ))
                $fail("Not One of the allowed domains");



    }
}
