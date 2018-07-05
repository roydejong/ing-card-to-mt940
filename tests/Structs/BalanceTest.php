<?php

namespace MT940\Tests\Structs;

use MT940\Structs\Balance;
use PHPUnit\Framework\TestCase;

class BalanceTest extends TestCase
{
    public function test__toString()
    {
        $balance = new Balance();
        $balance->amount = 1234.56;
        $balance->currencyCode = "USD";
        $balance->date = new \DateTime("1993-06-11");
        $balance->isCredit = false;

        $actual = strval($balance);
        $expected = "D930611USD1234,56";

        $this->assertEquals($expected, $actual);
    }
}
