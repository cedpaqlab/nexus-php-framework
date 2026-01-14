<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseRequest;

class CreateUserRequest extends BaseRequest
{
    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:2|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'string',
        ];
    }
}
