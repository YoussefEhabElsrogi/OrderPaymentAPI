<?php

namespace App\Exceptions;

use Exception;

class PaymentException extends Exception
{
    public function __construct(string $message = "Payment error", int $code = 400)
    {
        parent::__construct($message, $code);
    }
}
