<?php

declare(strict_types=1);

namespace Tests\Framework\Helpers;

use Tests\Support\TestCase;
use App\Services\Helpers\ArrayHelper;

class ArrayHelperTest extends TestCase
{
    public function testGet(): void
    {
        $array = ['user' => ['name' => 'John', 'age' => 30]];
        $value = ArrayHelper::get($array, 'user.name');
        $this->assertEquals('John', $value);
    }

    public function testGetWithDefault(): void
    {
        $array = ['user' => ['name' => 'John']];
        $value = ArrayHelper::get($array, 'user.email', 'default@example.com');
        $this->assertEquals('default@example.com', $value);
    }

    public function testHas(): void
    {
        $array = ['user' => ['name' => 'John']];
        $this->assertTrue(ArrayHelper::has($array, 'user.name'));
        $this->assertFalse(ArrayHelper::has($array, 'user.email'));
    }

    public function testSet(): void
    {
        $array = [];
        ArrayHelper::set($array, 'user.name', 'John');
        $this->assertEquals('John', $array['user']['name']);
    }

    public function testExcept(): void
    {
        $array = ['name' => 'John', 'email' => 'john@example.com', 'age' => 30];
        $result = ArrayHelper::except($array, ['age']);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('email', $result);
        $this->assertArrayNotHasKey('age', $result);
    }

    public function testOnly(): void
    {
        $array = ['name' => 'John', 'email' => 'john@example.com', 'age' => 30];
        $result = ArrayHelper::only($array, ['name', 'email']);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('email', $result);
        $this->assertArrayNotHasKey('age', $result);
    }
}
