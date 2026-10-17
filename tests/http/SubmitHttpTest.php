<?php

declare(strict_types=1);

namespace Tests;

final class SubmitHttpTest extends HttpTestCase
{
    private const TEST_EMAIL = 'http-test@example.co.za';

    public function setUp(): void
    {
        parent::setUp();
        $this->deleteByEmail(self::TEST_EMAIL);
    }

    // --------------------------------------------------------
    // Successful submission
    // --------------------------------------------------------

    public function test_valid_submission_inserts_row_and_redirects(): void
    {
        $token = $this->fetchCsrfToken();
        $before = $this->countEntries();

        $response = $this->post('/submit.php', [
            'csrf'    => $token,
            'name'    => 'HTTP Test User',
            'email'   => self::TEST_EMAIL,
            'phone'   => '082 123 4567',
            'message' => 'This is a valid submission from the HTTP test.',
        ]);

        $this->assertSame(
            303,
            $response['status'],
            'Expected 303 redirect, got ' . $response['status'] .
            ' — body: ' . substr($response['body'], 0, 300)
        );
        $this->assertSame('/', $response['location']);
        $this->assertSame($before + 1, $this->countEntries());

        $row = $this->findEntryByEmail(self::TEST_EMAIL);
        $this->assertNotNull($row);
        $this->assertSame('HTTP Test User', $row['name']);
        $this->assertSame('+27821234567', $row['phone'], 'Phone should be stored in E.164');
    }

    // --------------------------------------------------------
    // Method rejection
    // --------------------------------------------------------

    public function test_get_request_redirects_without_inserting(): void
    {
        $before = $this->countEntries();

        $response = $this->get('/submit.php');

        $this->assertSame(303, $response['status']);
        $this->assertSame('/', $response['location']);
        $this->assertSame($before, $this->countEntries(), 'GET must not insert a row');
    }

    // --------------------------------------------------------
    // CSRF
    // --------------------------------------------------------

    public function test_missing_csrf_token_rejects_submission(): void
    {
        $before = $this->countEntries();

        $response = $this->post('/submit.php', [
            'name'    => 'Should Not Save',
            'email'   => self::TEST_EMAIL,
            'phone'   => '0821234567',
            'message' => 'This should not be saved.',
        ]);

        $this->assertSame(303, $response['status']);
        $this->assertSame('/', $response['location']);
        $this->assertSame($before, $this->countEntries());
        $this->assertNull($this->findEntryByEmail(self::TEST_EMAIL));
    }

    public function test_tampered_csrf_token_rejects_submission(): void
    {
        $this->fetchCsrfToken();
        $before = $this->countEntries();

        $response = $this->post('/submit.php', [
            'csrf'    => 'deadbeef' . bin2hex(random_bytes(8)),
            'name'    => 'Tampered CSRF',
            'email'   => self::TEST_EMAIL,
            'phone'   => '0821234567',
            'message' => 'Should not be saved.',
        ]);

        $this->assertSame(303, $response['status']);
        $this->assertSame($before, $this->countEntries());
        $this->assertNull($this->findEntryByEmail(self::TEST_EMAIL));
    }

    // --------------------------------------------------------
    // Validation
    // --------------------------------------------------------

    public function test_validation_failure_redirects_without_inserting(): void
    {
        $token = $this->fetchCsrfToken();
        $before = $this->countEntries();

        $response = $this->post('/submit.php', [
            'csrf'    => $token,
            'name'    => 'A',
            'email'   => 'not-an-email',
            'phone'   => '123',
            'message' => 'short',
        ]);

        $this->assertSame(303, $response['status']);
        $this->assertSame($before, $this->countEntries());
        $this->assertNull($this->findEntryByEmail(self::TEST_EMAIL));
    }

    // --------------------------------------------------------
    // CSRF replay protection
    // --------------------------------------------------------

    public function test_csrf_token_cannot_be_reused_after_success(): void
    {
        $token = $this->fetchCsrfToken();

        $first = $this->post('/submit.php', [
            'csrf'    => $token,
            'name'    => 'First Submit',
            'email'   => self::TEST_EMAIL,
            'phone'   => '0821234567',
            'message' => 'First submit, valid message.',
        ]);
        $this->assertSame(303, $first['status']);
        $this->assertNotNull($this->findEntryByEmail(self::TEST_EMAIL));

        $this->deleteByEmail(self::TEST_EMAIL);

        $second = $this->post('/submit.php', [
            'csrf'    => $token,
            'name'    => 'Second Submit',
            'email'   => self::TEST_EMAIL,
            'phone'   => '0821234567',
            'message' => 'Second submit, should be rejected.',
        ]);
        $this->assertSame(303, $second['status']);
        $this->assertNull(
            $this->findEntryByEmail(self::TEST_EMAIL),
            'Replayed token should be rejected'
        );
    }

    // --------------------------------------------------------
    // Cleanup
    // --------------------------------------------------------

    public function tearDown(): void
    {
        $this->deleteByEmail(self::TEST_EMAIL);
    }
}