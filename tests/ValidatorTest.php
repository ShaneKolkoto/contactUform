<?php

declare(strict_types=1);

namespace Tests;

use App\Validator;

final class ValidatorTest extends TestCase
{
    // --------------------------------------------------------
    // Phone numbers — the trickiest part of the validation
    // --------------------------------------------------------

    public function test_sa_phone_number_normalization(): void
    {
        // [input, expected] pairs. A keyed map won't work here because
        // PHP silently casts numeric-string keys like '0821234567' to int.
        $cases = [
            ['0821234567',      '+27821234567'],
            ['082 123 4567',    '+27821234567'],
            ['082-123-4567',    '+27821234567'],
            ['(082) 123 4567',  '+27821234567'],
            ['+27 82 123 4567', '+27821234567'],
            ['+27821234567',    '+27821234567'],
            ['27821234567',     '+27821234567'],
            ['021 123 4567',    '+27211234567'], // landline
        ];

        foreach ($cases as [$input, $want]) {
            $this->assertSame($want, Validator::normalizeSaPhone($input), "input was '{$input}'");
        }
    }

    public function test_sa_phone_rejects_junk(): void
    {
        $bad = [
            '',
            'abc',
            '1234567890',      // no valid SA prefix
            '012345678',       // too short
            '08212345678901',  // too long
            '0921234567',      // 9 isn't a valid SA prefix
            '1821234567',      // 1 isn't either
            '082 123 456',     // too few digits after cleanup
            '+1 555 123 4567', // not SA at all
        ];

        foreach ($bad as $input) {
            $this->assertNull(Validator::normalizeSaPhone($input), "'{$input}' should be rejected");
        }
    }

    public function test_format_sa_phone_for_display(): void
    {
        $this->assertSame('082 123 4567', Validator::formatSaPhone('+27821234567'));
        $this->assertSame('+15551234567', Validator::formatSaPhone('+15551234567'));
    }

    // --------------------------------------------------------
    // Full form validation
    // --------------------------------------------------------

    public function test_valid_submission_passes(): void
    {
        $v = new Validator();
        $ok = $v->validate([
            'name'    => 'Thabo Mokoena',
            'email'   => 'thabo@example.co.za',
            'phone'   => '082 123 4567',
            'message' => 'Please can you send me a quote for a website?',
        ]);

        $this->assertTrue($ok, implode(' | ', $v->errors()));

        $clean = $v->clean();
        $this->assertSame('Thabo Mokoena', $clean['name']);
        $this->assertSame('+27821234567', $clean['phone_normalized']);
    }

    public function test_names_that_should_be_accepted(): void
    {
        $names = [
            'Thabo Mokoena',
            'Sarah van Wyk',
            "O'Brien",
            'Anne-Marie',
            'J. R. R. Tolkien',
            '李 明',
        ];

        foreach ($names as $name) {
            $this->assertValid(['name' => $name], "'{$name}' should be accepted");
        }
    }

    public function test_names_that_should_be_rejected(): void
    {
        $cases = [
            ''                   => 'required',
            'A'                  => 'at least 2',
            'Thabo123'           => 'only contain letters',
            '<script>x</script>' => 'only contain letters',
            str_repeat('A', 101) => 'no more than 100',
        ];

        foreach ($cases as $name => $fragment) {
            $errors = $this->validationErrors(['name' => (string) $name]);
            $this->assertArrayHasKey('name', $errors, "'{$name}' should have produced a name error");
            $this->assertTrue(
                stripos($errors['name'], $fragment) !== false,
                "'{$name}': expected error containing '{$fragment}', got '{$errors['name']}'"
            );
        }
    }

    public function test_email_rules(): void
    {
        foreach (['thabo@example.co.za', 'user+tag@sub.example.org'] as $email) {
            $this->assertValid(['email' => $email], "'{$email}' should be accepted");
        }

        foreach (['', 'not-an-email', 'a@b', 'no-tld@example'] as $email) {
            $errors = $this->validationErrors(['email' => $email]);
            $this->assertArrayHasKey('email', $errors, "'{$email}' should have been rejected");
        }
    }

    public function test_message_length_bounds(): void
    {
        $this->assertValid(['message' => str_repeat('x', 10)]);

        $errors = $this->validationErrors(['message' => str_repeat('x', 9)]);
        $this->assertArrayHasKey('message', $errors);

        $errors = $this->validationErrors(['message' => str_repeat('x', 5001)]);
        $this->assertArrayHasKey('message', $errors);
    }

    public function test_reports_all_errors_at_once(): void
    {
        $errors = $this->validationErrors([
            'name'    => '',
            'email'   => 'nope',
            'phone'   => '123',
            'message' => 'x',
        ]);

        foreach (['name', 'email', 'phone', 'message'] as $field) {
            $this->assertArrayHasKey($field, $errors, "missing error for {$field}");
        }
    }

    public function test_input_is_trimmed(): void
    {
        $v = new Validator();
        $v->validate([
            'name'    => '  Thabo Mokoena  ',
            'email'   => '  thabo@example.com  ',
            'phone'   => '082 123 4567',
            'message' => '  This message has extra whitespace.  ',
        ]);

        $clean = $v->clean();
        $this->assertSame('Thabo Mokoena', $clean['name']);
        $this->assertSame('thabo@example.com', $clean['email']);
        $this->assertSame('This message has extra whitespace.', $clean['message']);
    }

    // --------------------------------------------------------
    // Helpers
    // --------------------------------------------------------

    private function assertValid(array $overrides, string $message = ''): void
    {
        $errors = $this->validationErrors($overrides);
        $this->assertTrue($errors === [], $message ?: 'Expected valid, got: ' . implode(' | ', $errors));
    }

    private function validationErrors(array $overrides): array
    {
        $v = new Validator();
        $v->validate(array_merge([
            'name'    => 'Thabo Mokoena',
            'email'   => 'thabo@example.co.za',
            'phone'   => '0821234567',
            'message' => 'This is a valid message body.',
        ], $overrides));

        return $v->errors();
    }
}