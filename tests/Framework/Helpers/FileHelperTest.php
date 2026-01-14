<?php

declare(strict_types=1);

namespace Tests\Framework\Helpers;

use Tests\Support\TestCase;
use App\Services\Helpers\FileHelper;

class FileHelperTest extends TestCase
{
    private string $testDir;
    private string $testFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testDir = __DIR__ . '/../../../storage/test';
        $this->testFile = $this->testDir . '/test.txt';
        if (!is_dir($this->testDir)) {
            mkdir($this->testDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testFile)) {
            unlink($this->testFile);
        }
        if (is_dir($this->testDir)) {
            rmdir($this->testDir);
        }
        parent::tearDown();
    }

    public function testExists(): void
    {
        file_put_contents($this->testFile, 'test');
        $this->assertTrue(FileHelper::exists($this->testFile));
        $this->assertFalse(FileHelper::exists($this->testDir . '/nonexistent.txt'));
    }

    public function testGet(): void
    {
        $content = 'Test content';
        file_put_contents($this->testFile, $content);
        $this->assertEquals($content, FileHelper::get($this->testFile));
    }

    public function testPut(): void
    {
        $content = 'New content';
        FileHelper::put($this->testFile, $content);
        $this->assertFileExists($this->testFile);
        $this->assertEquals($content, file_get_contents($this->testFile));
    }

    public function testDelete(): void
    {
        file_put_contents($this->testFile, 'test');
        $this->assertTrue(FileHelper::delete($this->testFile));
        $this->assertFalse(FileHelper::exists($this->testFile));
    }

    public function testSize(): void
    {
        $content = 'Test content';
        file_put_contents($this->testFile, $content);
        $this->assertEquals(strlen($content), FileHelper::size($this->testFile));
    }

    public function testExtension(): void
    {
        $this->assertEquals('txt', FileHelper::extension($this->testFile));
    }

    public function testName(): void
    {
        $this->assertEquals('test', FileHelper::name($this->testFile));
    }
}
