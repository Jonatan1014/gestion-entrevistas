<?php

namespace App\Enums;

enum TestType: string
{
    case NUMERIC = 'numeric';
    case TEXT = 'text';
    case MULTIPLE_CHOICE = 'multiple_choice';
}
