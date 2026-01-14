<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseRequest;

class UpdateRoleRequest extends BaseRequest
{
    protected function rules(): array
    {
        return [
            'role' => 'required|string',
        ];
    }
}
