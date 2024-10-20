<?php

namespace App\Service\Exceptions;

use App\Entity\Enums\OrderStatus;
use Throwable;

class OrderCompletedException extends \Exception
{
    public function __construct(string $message = "Order is ". OrderStatus::COMPLETED->name, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}