<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseRequest;

class UpdateUserRequest extends BaseRequest
{
    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:2|max:100',
            'email' => 'required|email',
            'password' => 'string|min:8',
        ];
    }
}
