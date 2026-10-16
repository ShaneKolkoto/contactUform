<?php

declare(strict_types=1);

namespace App;

/**
 * Validates contact-form input and normalizes South African phone numbers.
 *
 * Typical use:
 *
 *     $validator = new Validator();
 *     if ($validator->validate($_POST)) {
 *         $data = $validator->clean();   // safe to save
 *     } else {
 *         $errors = $validator->errors(); // field => message
 *     }
 */
final class Validator
{
    /** @var array<string, string> Field name → user-facing error message */
    private array $errors = [];

    /** @var array<string, mixed> Cleaned input, ready to save */
    private array $clean = [];

    private const NAME_MIN = 2;
    private const NAME_MAX = 100;
    private const EMAIL_MAX = 255;
    private const MESSAGE_MIN = 10;
    private const MESSAGE_MAX = 5000;

    /**
     * Runs every field rule. Returns true if all pass.
     * Check errors() and clean() afterwards.
     */
    public function validate(array $input): bool
    {
        $this->errors = [];
        $this->clean = [];

        $name = trim((string) ($input['name'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $phone = trim((string) ($input['phone'] ?? ''));
        $message = trim((string) ($input['message'] ?? ''));

        $this->checkName($name);
        $this->checkEmail($email);
        $this->checkPhone($phone);
        $this->checkMessage($message);

        $this->clean['name'] = $name;
        $this->clean['email'] = $email;
        $this->clean['message'] = $message;

        return empty($this->errors);
    }

    /** @return array<string, string> */
    public function errors(): array
    {
        return $this->errors;
    }

    /** @return array<string, mixed> */
    public function clean(): array
    {
        return $this->clean;
    }

    // --------------------------------------------------------
    // Field rules
    // --------------------------------------------------------

    private function checkName(string $name): void
    {
        if ($name === '') {
            $this->errors['name'] = 'Name is required.';
            return;
        }

        $length = mb_strlen($name);

        if ($length < self::NAME_MIN) {
            $this->errors['name'] = 'Name must be at least ' . self::NAME_MIN . ' characters.';
            return;
        }

        if ($length > self::NAME_MAX) {
            $this->errors['name'] = 'Name must be no more than ' . self::NAME_MAX . ' characters.';
            return;
        }

        // Letters (any language), spaces, hyphens, apostrophes, periods.
        // Blocks digits, HTML, emoji and other noise.
        if (!preg_match("/^[\p{L}\p{M}\s'\-\.]+$/u", $name)) {
            $this->errors['name'] = 'Name may only contain letters, spaces, hyphens, apostrophes and periods.';
        }
    }

    private function checkEmail(string $email): void
    {
        if ($email === '') {
            $this->errors['email'] = 'Email is required.';
            return;
        }

        if (mb_strlen($email) > self::EMAIL_MAX) {
            $this->errors['email'] = 'Email is too long.';
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Please enter a valid email address.';
        }
    }

    private function checkPhone(string $phone): void
    {
        if ($phone === '') {
            $this->errors['phone'] = 'Phone number is required.';
            return;
        }

        $formatted = self::normalizeSaPhone($phone);

        if ($formatted === null) {
            $this->errors['phone'] = 'Please enter a valid South African phone number (e.g. 082 123 4567).';
            return;
        }

        // Save the normalized E.164 form, not what the user typed
        $this->clean['phone_normalized'] = $formatted;
    }

    private function checkMessage(string $message): void
    {
        if ($message === '') {
            $this->errors['message'] = 'Message is required.';
            return;
        }

        $length = mb_strlen($message);

        if ($length < self::MESSAGE_MIN) {
            $this->errors['message'] = 'Message must be at least ' . self::MESSAGE_MIN . ' characters.';
            return;
        }

        if ($length > self::MESSAGE_MAX) {
            $this->errors['message'] = 'Message must be no more than ' . self::MESSAGE_MAX . ' characters.';
        }
    }

    // --------------------------------------------------------
    // SA phone helpers
    // --------------------------------------------------------

    /**
     * Turns any reasonable SA phone format into E.164 (+27XXXXXXXXX).
     *
     * Accepts: 082 123 4567, 0821234567, 082-123-4567, (082) 123 4567,
     *          +27 82 123 4567, +27821234567, 27821234567.
     *
     * Returns null if the number can't be parsed.
     */
    public static function normalizeSaPhone(string $phone): ?string
    {
        // Strip spaces, dashes, dots and parentheses first
        $phone = preg_replace('/[\s\-\.\(\)]+/', '', $phone);

        if ($phone === null || $phone === '') {
            return null;
        }

        if (str_starts_with($phone, '+')) {
            $phone = substr($phone, 1);
        }

        if (!ctype_digit($phone)) {
            return null;
        }

        // Reduce to the 9-digit national number (without leading 0 or 27)
        if (strlen($phone) === 11 && str_starts_with($phone, '27')) {
            $nationalNumber = substr($phone, 2);
        } elseif (strlen($phone) === 10 && str_starts_with($phone, '0')) {
            $nationalNumber = substr($phone, 1);
        } else {
            return null;
        }

        // SA numbers start with 2-8: 2/3 are landlines, 6/7/8 are mobile
        if (!preg_match('/^[2-8][0-9]{8}$/', $nationalNumber)) {
            return null;
        }

        return '+27' . $nationalNumber;
    }

    /**
     * Formats an E.164 SA number for display, e.g. +27821234567 → 082 123 4567.
     * Returns the input unchanged if it isn't a recognized SA number.
     */
    public static function formatSaPhone(string $phone): string
    {
        if (!preg_match('/^\+27(\d{9})$/', $phone, $matches)) {
            return $phone;
        }

        $number = $matches[1];

        return sprintf(
            '0%s %s %s',
            substr($number, 0, 2),
            substr($number, 2, 3),
            substr($number, 5, 4)
        );
    }
}