<?php

namespace Tests\Unit;

use App\Services\MailProvider\MailProviderDetector;
use PHPUnit\Framework\TestCase;

class MailProviderDetectorTest extends TestCase
{
    private MailProviderDetector $detector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->detector = new MailProviderDetector();
    }

    public function test_google_workspace_is_detected(): void
    {
        $result = $this->detector->detect(
            'example.com',
            [
                [
                    'host' => 'aspmx.l.google.com',
                    'priority' => 10,
                ],
            ]
        );

        $this->assertSame(
            'google',
            $result['provider']
        );

        $this->assertSame(
            'detected',
            $result['status']
        );
    }

    public function test_microsoft_365_is_detected(): void
    {
        $result = $this->detector->detect(
            'example.com',
            [
                [
                    'host' => 'example-com.mail.protection.outlook.com',
                    'priority' => 10,
                ],
            ]
        );

        $this->assertSame(
            'microsoft',
            $result['provider']
        );
    }

    public function test_other_provider_is_detected(): void
    {
        $result = $this->detector->detect(
            'example.com',
            [
                [
                    'host' => 'mail.example.com',
                    'priority' => 10,
                ],
            ]
        );

        $this->assertSame(
            'other',
            $result['provider']
        );
    }

    public function test_missing_mx_is_not_detected(): void
    {
        $result = $this->detector->detect(
            'example.com',
            []
        );

        $this->assertSame(
            'not_detected',
            $result['provider']
        );
    }
}
