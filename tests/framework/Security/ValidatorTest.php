<?php

declare(strict_types=1);

namespace Tests\Framework\Security;

use Tests\Support\TestCase;
use App\Services\Security\Validator;

class ValidatorTest extends TestCase
{
    private Validator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new Validator();
    }

    public function testRequiredRule(): void
    {
        $data = [];
        $rules = ['name' => 'required'];
        $errors = $this->validator->validate($data, $rules);
        $this->assertArrayHasKey('name', $errors);
    }

    public function testEmailRule(): void
    {
        $data = ['email' => 'invalid-email'];
        $rules = ['email' => 'email'];
        $errors = $this->validator->validate($data, $rules);
        $this->assertArrayHasKey('email', $errors);
    }

    public function testMinRule(): void
    {
        $data = ['password' => '123'];
        $rules = ['password' => 'min:8'];
        $errors = $this->validator->validate($data, $rules);
        $this->assertArrayHasKey('password', $errors);
    }

    public function testMaxRule(): void
    {
        $data = ['name' => str_repeat('a', 101)];
        $rules = ['name' => 'max:100'];
        $errors = $this->validator->validate($data, $rules);
        $this->assertArrayHasKey('name', $errors);
    }

    public function testNumericRule(): void
    {
        $data = ['age' => 'not-a-number'];
        $rules = ['age' => 'numeric'];
        $errors = $this->validator->validate($data, $rules);
        $this->assertArrayHasKey('age', $errors);
    }

    public function testStringRule(): void
    {
        $data = ['name' => 123];
        $rules = ['name' => 'string'];
        $errors = $this->validator->validate($data, $rules);
        $this->assertArrayHasKey('name', $errors);
    }

    public function testConfirmedRule(): void
    {
        $data = ['password' => 'secret', 'password_confirmation' => 'different'];
        $rules = ['password' => 'confirmed'];
        $errors = $this->validator->validate($data, $rules);
        $this->assertArrayHasKey('password', $errors);
    }

    public function testValidDataPasses(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 30,
        ];
        $rules = [
            'name' => 'required|string|max:100',
            'email' => 'required|email',
            'age' => 'numeric',
        ];
        $errors = $this->validator->validate($data, $rules);
        $this->assertEmpty($errors);
    }

    public function testUniqueRuleWithPropel(): void
    {
        if (!class_exists(\Propel\Runtime\Propel::class)) {
            $this->markTestSkipped('Propel is not installed');
            return;
        }

        $this->markTestIncomplete('Requires Propel models to be generated and database setup');
    }
}
