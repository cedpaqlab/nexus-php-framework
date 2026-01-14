<?php

declare(strict_types=1);

namespace App\Services\Security;

use App\Models\UserQuery;

class Validator
{
    public function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleSet) {
            $rulesArray = is_string($ruleSet) ? explode('|', $ruleSet) : $ruleSet;
            $value = $data[$field] ?? null;

            foreach ($rulesArray as $rule) {
                $error = $this->validateRule($field, $value, $rule, $data);
                if ($error !== null) {
                    $errors[$field][] = $error;
                }
            }
        }

        return $errors;
    }

    private function validateRule(string $field, mixed $value, string $rule, array $data): ?string
    {
        if (str_contains($rule, ':')) {
            [$ruleName, $param] = explode(':', $rule, 2);
        } else {
            $ruleName = $rule;
            $param = null;
        }

        return match ($ruleName) {
            'required' => $this->validateRequired($field, $value),
            'email' => $this->validateEmail($field, $value),
            'min' => $this->validateMin($field, $value, (int) $param),
            'max' => $this->validateMax($field, $value, (int) $param),
            'numeric' => $this->validateNumeric($field, $value),
            'string' => $this->validateString($field, $value),
            'confirmed' => $this->validateConfirmed($field, $value, $data, $param),
            'unique' => $this->validateUnique($field, $value, $param),
            'in' => $this->validateIn($field, $value, $param),
            default => null,
        };
    }

    private function validateRequired(string $field, mixed $value): ?string
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            return "The {$field} field is required.";
        }
        return null;
    }

    private function validateEmail(string $field, mixed $value): ?string
    {
        if ($value !== null && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return "The {$field} must be a valid email address.";
        }
        return null;
    }

    private function validateMin(string $field, mixed $value, int $min): ?string
    {
        if ($value !== null) {
            $length = is_string($value) ? strlen($value) : (is_numeric($value) ? $value : count((array) $value));
            if ($length < $min) {
                return "The {$field} must be at least {$min} characters.";
            }
        }
        return null;
    }

    private function validateMax(string $field, mixed $value, int $max): ?string
    {
        if ($value !== null) {
            $length = is_string($value) ? strlen($value) : (is_numeric($value) ? $value : count((array) $value));
            if ($length > $max) {
                return "The {$field} must not exceed {$max} characters.";
            }
        }
        return null;
    }

    private function validateNumeric(string $field, mixed $value): ?string
    {
        if ($value !== null && !is_numeric($value)) {
            return "The {$field} must be a number.";
        }
        return null;
    }

    private function validateString(string $field, mixed $value): ?string
    {
        if ($value !== null && !is_string($value)) {
            return "The {$field} must be a string.";
        }
        return null;
    }

    private function validateConfirmed(string $field, mixed $value, array $data, ?string $param): ?string
    {
        $confirmationField = $param ?? $field . '_confirmation';
        if ($value !== $data[$confirmationField] ?? null) {
            return "The {$field} confirmation does not match.";
        }
        return null;
    }

    private function validateUnique(string $field, mixed $value, ?string $param): ?string
    {
        if ($value === null || $param === null) {
            return null;
        }

        [$table, $column] = explode(',', $param);
        $table = trim($table);
        $column = trim($column);

        if ($table !== 'users' || $column !== 'email') {
            return null;
        }

        try {
            $count = UserQuery::create()->filterByEmail($value)->count();
            if ($count > 0) {
                return "The {$field} has already been taken.";
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    private function validateIn(string $field, mixed $value, ?string $param): ?string
    {
        if ($value === null || $param === null) {
            return null;
        }

        $allowedValues = array_map('trim', explode(',', $param));

        if (!in_array($value, $allowedValues, true)) {
            return "The {$field} must be one of: " . implode(', ', $allowedValues);
        }

        return null;
    }
}
