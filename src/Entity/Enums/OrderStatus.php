<?php

namespace App\Entity\Enums;

enum OrderStatus: string
{
    case CREATED = 'created';
    case COMPLETED = 'completed';
    case CANCELED = 'canceled';
}