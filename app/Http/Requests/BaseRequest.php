<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Request;
use App\Http\Response;
use App\Services\Security\Validator;

abstract class BaseRequest
{
    protected Request $request;
    protected Validator $validator;
    protected Response $response;

    public function __construct(Request $request, Validator $validator, Response $response)
    {
        $this->request = $request;
        $this->validator = $validator;
        $this->response = $response;
    }

    abstract protected function rules(): array;

    public function validate(): array|Response
    {
        $rules = $this->rules();
        $data = $this->request->all();

        $errors = $this->validator->validate($data, $rules);

        if (!empty($errors)) {
            return $this->response->json(['errors' => $errors], 422);
        }

        // Filter data to only include fields defined in rules (whitelist approach)
        $allowedFields = array_keys($rules);
        $filteredData = array_intersect_key($data, array_flip($allowedFields));

        return $filteredData;
    }

    protected function validated(): array
    {
        $result = $this->validate();
        if ($result instanceof Response) {
            $result->send();
            exit;
        }

        return $result;
    }
}
