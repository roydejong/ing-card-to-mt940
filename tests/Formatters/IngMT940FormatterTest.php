<?php

namespace MT940\Tests\Formatters;

use MT940\Formatters\IngMT940Formatter;
use MT940\Structs\Balance;
use MT940\Structs\Transaction;
use PHPUnit\Framework\TestCase;

class IngMT940FormatterTest extends TestCase
{
    public function testMinimalFormatWithoutTransactions()
    {
        $balance = new Balance(1234.56, true, new \DateTime("2000-12-31"), "USD");

        $formatter = new IngMT940Formatter();
        $formatter->setTransactionReferenceNumber("SAMPLE_TRN");
        $formatter->setAccountIdentification("SAMPLE_ACCOUNT");
        $formatter->setOpeningBalance($balance);
        $formatter->setClosingBalanceBooked($balance);
        $formatter->setClosingBalanceAvailable($balance);
        $formatter->setAccountOwnerInfo("/SUM/0/0/0,00/0,00/");
        $formatter->addForwardAvailableBalance($balance);
        $formatter->addForwardAvailableBalance($balance);

        $actual = $formatter->formatToString();

        $expected = file_get_contents(__DIR__ . "/../Samples/mt940-ing-no-transactions.txt");

        $this->assertEquals($expected, $actual);
    }

    public function testMinimalFormatWithTransaction()
    {
        $testBalanceStart = 6238.88;
        $testTrxAmount = 181.50;

        $balanceStart = new Balance($testBalanceStart, true, new \DateTime("2018-07-03"), "EUR");
        $balanceEnd = new Balance($testBalanceStart - $testTrxAmount, true, new \DateTime("2018-07-04"), "EUR");
        $balanceEndPlusOne = new Balance($testBalanceStart - $testTrxAmount, true, new \DateTime("2018-07-05"), "EUR");
        $balanceEndPlusTwo = new Balance($testBalanceStart - $testTrxAmount, true, new \DateTime("2018-07-06"), "EUR");

        $formatter = new IngMT940Formatter();
        $formatter->setTransactionReferenceNumber("PXXXXXX00000000X");
        $formatter->setAccountIdentification("NL04INGBXXXXXXXXXXEUR");
        $formatter->setOpeningBalance($balanceStart);
        $formatter->addTransaction(new Transaction([
            "valueDate" => new \DateTime("2018-07-04"),
            "entryDate" => new \DateTime("2018-07-04"),
            "isCredit" => false,
            "amount" => $testTrxAmount,
            "type" => Transaction::TYPE_NON_SWIFT_TRANSFER,
            "typeIdentCode" => "TRF",
            "referenceCustomer" => "EREF",
            "referenceInstitution" => "XXXXXXXXXXXXXX",
            "supplementaryDetails" => "/TRCD/00100/",
            "relatedAccountOwnerInfo" => "/EREF/20180200//CNTP/NL95ABNAXXXXXXXXXX/ABNANL2A/Counterparty\r\n123///REMI/USTD//20180200/"
        ]));
        $formatter->setClosingBalanceBooked($balanceEnd);
        $formatter->setClosingBalanceAvailable($balanceEnd);
        $formatter->addForwardAvailableBalance($balanceEndPlusOne);
        $formatter->addForwardAvailableBalance($balanceEndPlusTwo);
        $formatter->setAccountOwnerInfo("/SUM/1/0/181,50/0,00/");
        $actual = $formatter->formatToString();

        $expected = file_get_contents(__DIR__ . "/../Samples/mt940-ing-with-transaction.txt");

        $this->assertEquals($expected, $actual);
    }
}
