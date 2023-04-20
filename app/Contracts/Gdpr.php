<?php

namespace App\Contracts;

interface Gdpr
{
    /**
     * Check if the user should be anonymised.
     *
     * @return bool True if yes, false if no
     */
    public function shouldBeAnonymised(): bool;

    /**
     * Anonymise the account.
     *
     * @return string The anonomised username/identifier
     */
    public function anonymise(): string;

    /**
     * Transform the user model into an array suitable for a JSON response.
     *
     * @return array The attribs/relations of the user suitable for GDPR export
     */
    public function toGdpr(): array;
}
