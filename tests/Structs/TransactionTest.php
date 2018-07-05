<?php

namespace MT940\Tests\Structs;

use MT940\Structs\StructDataException;
use MT940\Structs\Transaction;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
    public function test__toString()
    {
        $systemYear = date("Y");
        $systemYearShort = date("y");

        $valueDate = new \DateTime("{$systemYear}-12-15");
        $entryDate = new \DateTime("{$systemYear}-12-16");

        $transaction = new Transaction();
        $transaction->valueDate = $valueDate;
        $transaction->entryDate = $entryDate;
        $transaction->isCredit = false;
        $transaction->fundsCode = "USD";
        $transaction->amount = 1234.56;
        $transaction->type = Transaction::TYPE_SWIFT_TRANSFER; // S
        $transaction->typeIdentCode =  "MSC"; // Miscellaneous
        $transaction->referenceCustomer = "MYREF";
        $transaction->referenceInstitution = "12345678901234";
        $transaction->supplementaryDetails = "SUPP";

        $actual = strval($transaction);
        $expected = "{$systemYearShort}12151216DD1234,56SMSCMYREF//12345678901234\r\nSUPP";

        $this->assertEquals($expected, $actual);
    }

    public function test__toString_swiftExample()
    {
        $systemYear = date("Y");

        $transaction = new Transaction();
        $transaction->valueDate = new \DateTime("2009-01-23");
        $transaction->entryDate = new \DateTime("{$systemYear}-01-22");
        $transaction->isCredit = true;
        $transaction->fundsCode = null;
        $transaction->amount = 3500.25;
        $transaction->type = Transaction::TYPE_FIRST_ADVICE;
        $transaction->typeIdentCode =  "CHK";
        $transaction->referenceCustomer = "304955";
        $transaction->referenceInstitution = "4958843";
        $transaction->supplementaryDetails = "ADDITIONAL INFORMATION";

        $actual = ":61:" . strval($transaction);
        $expected = file_get_contents(__DIR__ . "/../Samples/61-transaction-sample-swift.txt");

        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException MT940\Structs\StructDataException
     * @expectedExceptionMessage Entry Date must be refer to the current system year
     */
    public function test__toString_entryDateValidation()
    {
        // The SWIFT System validates subfield 2, Entry Date (Date in reduced ISO form), using current System Year (Error code(s): T50).

        $systemYear = "1990";

        $valueDate = new \DateTime("{$systemYear}-12-15");
        $entryDate = new \DateTime("{$systemYear}-12-16");

        $transaction = new Transaction();
        $transaction->valueDate = $valueDate;
        $transaction->entryDate = $entryDate;
        $transaction->__toString();
    }

    /**
     * @expectedException MT940\Structs\StructDataException
     * @expectedExceptionMessage Reference for the Account Owner: At least one valid character other than a blank must be present.
     */
    public function test__toString_blankCustomerRefValidation()
    {
        // Subfield 7, Reference for the Account Owner:
        // At least one valid character other than a blank must be present.

        $systemYear = date("Y");

        $valueDate = new \DateTime("{$systemYear}-12-15");
        $entryDate = new \DateTime("{$systemYear}-12-16");

        $transaction = new Transaction();
        $transaction->valueDate = $valueDate;
        $transaction->entryDate = $entryDate;
        $transaction->isCredit = false;
        $transaction->fundsCode = "USD";
        $transaction->amount = 1234.56;
        $transaction->type = Transaction::TYPE_SWIFT_TRANSFER; // S
        $transaction->typeIdentCode =  "MSC"; // Miscellaneous
        $transaction->referenceCustomer = " ";

        $transaction->__toString();
    }
}
