<?php

declare(strict_types=1);

namespace Tests\Framework\Security;

use Tests\Support\TestCase;
use App\Services\Security\Sanitizer;

class SanitizerTest extends TestCase
{
    public function testInputSanitization(): void
    {
        $input = '<script>alert("xss")</script>Hello';
        $sanitized = Sanitizer::input($input);
        $this->assertStringNotContainsString('<script>', $sanitized);
        $this->assertStringContainsString('Hello', $sanitized);
    }

    public function testOutputSanitization(): void
    {
        $output = '<div>Test</div>';
        $sanitized = Sanitizer::output($output);
        $this->assertStringNotContainsString('<div>', $sanitized);
    }

    public function testEmailSanitization(): void
    {
        $email = '  test@example.com  ';
        $sanitized = Sanitizer::email($email);
        $this->assertEquals('test@example.com', $sanitized);
    }

    public function testUrlSanitization(): void
    {
        $url = '  https://example.com  ';
        $sanitized = Sanitizer::url($url);
        $this->assertStringContainsString('https://example.com', $sanitized);
    }

    public function testIntSanitization(): void
    {
        $value = '123abc';
        $sanitized = Sanitizer::int($value);
        $this->assertEquals(123, $sanitized);
    }

    public function testFloatSanitization(): void
    {
        $value = '12.34abc';
        $sanitized = Sanitizer::float($value);
        $this->assertEquals(12.34, $sanitized);
    }

    public function testArraySanitization(): void
    {
        $array = ['<script>alert("xss")</script>', 'normal text'];
        $sanitized = Sanitizer::array($array);
        $this->assertStringNotContainsString('<script>', $sanitized[0]);
        $this->assertEquals('normal text', $sanitized[1]);
    }
}
