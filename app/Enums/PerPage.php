<?php

namespace App\Enums;

enum PerPage: int
{
    case Ten = 10;
    case TwentyFive = 25;
    case Fifty = 50;
    case OneHundred = 100;
    case TwoHundredFifty = 250;

    public function label(): string
    {
        return (string) $this->value;
    }
}
