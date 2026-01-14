<?php

declare(strict_types=1);

namespace App\Http;

enum HttpStatusCode: int
{
    case OK = 200;
    case CREATED = 201;
    case NO_CONTENT = 204;
    case BAD_REQUEST = 400;
    case UNAUTHORIZED = 401;
    case FORBIDDEN = 403;
    case NOT_FOUND = 404;
    case METHOD_NOT_ALLOWED = 405;
    case CONFLICT = 409;
    case UNPROCESSABLE_ENTITY = 422;
    case INTERNAL_SERVER_ERROR = 500;
    case BAD_GATEWAY = 502;
    case SERVICE_UNAVAILABLE = 503;

    public function value(): int
    {
        return $this->value;
    }
}
