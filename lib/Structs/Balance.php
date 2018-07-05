<?php

namespace MT940\Structs;

/**
 * Represents an account balance on a certain date.
 */
class Balance
{
    /**
     * Date on which the balance was measured.
     * If set to NULL, today is implied.
     *
     * @var \DateTime|null
     */
    public $date = null;

    /**
     * True for credit, false for debit.
     *
     * @default false
     * @var bool
     */
    public $isCredit = false;

    /**
     * ISO currency code.
     *
     * @default EUR
     * @var string
     */
    public $currencyCode = "EUR";

    /**
     * Balance amount on date.
     *
     * @default 0
     * @var float
     */
    public $amount = 0;

    /**
     * Balance constructor.
     *
     * @param float $amount
     * @param bool $isCredit
     * @param \DateTime|null $date
     * @param string $currencyCode
     */
    public function __construct(float $amount = 0, bool $isCredit = false, ?\DateTime $date = null, string $currencyCode = "EUR")
    {
        $this->amount = $amount;
        $this->isCredit = $isCredit;
        $this->date = $date;
        $this->currencyCode = $currencyCode;
    }

    /**
     * Safe helper function for retrieving balance date.
     * If no explicit date is provided, today (now) is assumed.
     *
     * @return \DateTime
     */
    public function getBalanceDate(): \DateTime
    {
        if (empty($this->date)) {
            $this->date = new \DateTime('now');
        }

        $this->date->setTime(0, 0, 0, 0);
        return $this->date;
    }

    /**
     * Gets whether this is a credit balance.
     *
     * @return bool
     */
    public function getIsCredit(): bool
    {
        if (round($this->amount, 3) === 0.0) {
            // For a zero balance, always use C (Credit) indicator.
            // (ING: "Als het bedrag gelijk is aan 0, dan wordt de indicator C gebruikt.")
            return true;
        }

        return $this->isCredit;
    }

    /**
     * Gets whether this is a debit balance.
     *
     * @return bool
     */
    public function getIsDebit(): bool
    {
        return !$this->getIsCredit();
    }

    /**
     * Converts this balance state to an MT940-compatible string value.
     *
     * @format 1!aYYMMDD3!a15d
     * @return string
     */
    public function __toString(): string
    {
        /**
         * FORMAT:
         * 1!aYYMMDD3!a15d
         *
         * VALIDATION:
         *  - Date must be a valid date expressed as YYMMDD (Error code(s): T50).
         *  - Currency must be a valid ISO 4217 currency code (Error code(s): T52).
         *  - The integer part of Amount must contain at least one digit. The decimal comma ',' is mandatory and is
         *    included in the maximum length. The number of digits following the comma must not exceed the maximum
         *    number allowed for that specific currency as specified in ISO 4217 (Error code(s): C03, T40, T43).
         *
         * USAGE OF FIELDS:
         *  - The D/C Mark, Currency and Amount in this field must always be the same as the D/C Mark, Currency and
         *    Amount in field 62a (closing balance) of the previous customer statement message for this account.
         *  - The first customer statement message for a specified period must contain field 60F (first opening
         *    balance); additional statement messages for the same statement period must contain field 60M
         *    (intermediate opening balance).
         */

        $output = "";

        $output .= $this->isCredit ? "C" : "D";
        $output .= $this->getBalanceDate()->format('ymd');
        $output .= strtoupper($this->currencyCode ?? "EUR");
        $output .= number_format($this->amount, 2, ',', '');

        return $output;
    }
}