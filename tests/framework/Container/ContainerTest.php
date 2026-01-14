<?php

declare(strict_types=1);

namespace Tests\Framework\Container;

use Tests\Support\TestCase;
use Container;

class ContainerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
    }

    public function testBindAndResolve(): void
    {
        $this->container->bind('test', fn() => 'resolved');
        $result = $this->container->get('test');
        $this->assertEquals('resolved', $result);
    }

    public function testSingletonReturnsSameInstance(): void
    {
        $this->container->singleton('singleton', fn() => new \stdClass());
        $instance1 = $this->container->get('singleton');
        $instance2 = $this->container->get('singleton');
        $this->assertSame($instance1, $instance2);
    }

    public function testFactoryReturnsNewInstance(): void
    {
        $this->container->factory('factory', fn() => new \stdClass());
        $instance1 = $this->container->get('factory');
        $instance2 = $this->container->get('factory');
        $this->assertNotSame($instance1, $instance2);
    }

    public function testAutoResolveClass(): void
    {
        $instance = $this->container->get(TestClass::class);
        $this->assertInstanceOf(TestClass::class, $instance);
    }

    public function testAutoResolveWithDependencies(): void
    {
        $instance = $this->container->get(TestClassWithDependency::class);
        $this->assertInstanceOf(TestClassWithDependency::class, $instance);
        $this->assertInstanceOf(TestClass::class, $instance->dependency);
    }

    public function testHasReturnsTrueForBoundService(): void
    {
        $this->container->bind('test', fn() => 'value');
        $this->assertTrue($this->container->has('test'));
    }

    public function testHasReturnsTrueForExistingClass(): void
    {
        $this->assertTrue($this->container->has(\stdClass::class));
    }

    public function testHasReturnsFalseForNonExistent(): void
    {
        $this->assertFalse($this->container->has('NonExistentClass'));
    }
}

class TestClass
{
}

class TestClassWithDependency
{
    public function __construct(public TestClass $dependency)
    {
    }
}
