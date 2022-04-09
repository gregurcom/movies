<?php
declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Validator\ConstraintViolationListInterface;

final class ValidationService
{
    public function getErrorMessages(ConstraintViolationListInterface $errors, array $fields): array
    {
        $errorMessages = $fields;

        foreach ($errors as $violation) {
            $errorMessages[$violation->getPropertyPath()] = $violation->getMessage();
        }

        return $errorMessages;
    }
}