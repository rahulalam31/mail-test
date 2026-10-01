<?php

namespace Tests\Unit;

use App\Services\Domain\DomainNormalizer;
use PHPUnit\Framework\TestCase;

class DomainNormalizerTest extends TestCase
{
    private DomainNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->normalizer = new DomainNormalizer();
    }

    public function test_domain_is_normalized(): void
    {
        $this->assertSame(
            'example.com',
            $this->normalizer->normalize(
                'Example.COM'
            )
        );
    }

    public function test_email_domain_is_extracted(): void
    {
        $this->assertSame(
            'example.com',
            $this->normalizer->normalize(
                'user@example.com'
            )
        );
    }

    public function test_url_is_normalized(): void
    {
        $this->assertSame(
            'example.com',
            $this->normalizer->normalize(
                'https://example.com/some/path'
            )
        );
    }

    public function test_invalid_domain_returns_null(): void
    {
        $this->assertNull(
            $this->normalizer->normalize(
                'not a valid domain'
            )
        );
    }
}
