<?php
declare(strict_types=1);

namespace SOLPI\Helpers;

final class ValidationHelper
{
    public static function email(string $email): bool
    {
        return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function required(array $data, array $fields): bool
    {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return false;
            }
        }
        return true;
    }
}

