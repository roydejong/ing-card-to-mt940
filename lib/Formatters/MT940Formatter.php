<?php

namespace MT940\Formatters;
use MT940\Structs\Balance;

/**
 * Formatter for MT940 as defined by the SWIFT standards,
 * with supplements for common properties shared between substandards.
 *
 * Based on SR 2017 (MT 940 Customer Statement Message) by Swift.
 * @see https://www2.swift.com/mystandards/#/mt/2017.November/940/940!content
 */
class MT940Formatter extends Formatter
{
    /**
     * @inheritdoc
     */
    public function getId(): string
    {
        return "mt940";
    }

    const CS2_EOL = "\r\n";

    const C_DATE_BOOKED = 0;
    const C_DATE_OCCURED = 1;
    const C_SUPPLIER_NAME = 2;
    const C_SUPPLIER_PLACE = 3;
    const C_SUPPLIER_STATE = 4;
    const C_SUPPLIER_POSTCODE = 5;
    const C_MCC_SIC = 6;
    const C_MCC_DESCR = 7;
    const C_AMOUNT_ORIGINAL_EUROS = 8;
    const C_AMOUNT_ORIGINAL_CENTS = 9;
    const C_CURRENCY = 10;
    const C_EXCHANGE_RATE_NUMBERS = 11;
    const C_EXCHANGE_RATE_DECIMALS = 12;
    const C_AMOUNT_INVOICED_EUROS = 13;
    const C_AMOUNT_INVOICED_CENTS = 14;
    const C_MEMO = 15;
    const C_DEBIT_CREDIT = 16;
    const C_REFERENCE = 17;
    const C_DATE_OVERVIEW = 18;
    const C_NAME_ON_CARD = 19;
    const C_CARD_NUMBER = 20;

    /**
     * Tag 20: Transaction Reference Number (TRN). Mandatory field.
     *
     * @format 16x
     * @multiplicity 1..1
     * @definition This field specifies the reference assigned by the Sender to unambiguously identify the message.
     * @validation This field must not start or end with a slash '/' and must not contain two consecutive slashes '//' (Error code(s): T26).
     * @usage The TRN may be the same or different for the separate messages of a statement consisting of several messages.
     */
    const TAG_20_TRANSACTION_REFERENCE = ":20:";

    /**
     * Tag 21: Related Reference.
     *
     * @format 16x
     * @multiplicity 0..1
     * @definition If the MT 940 is sent in response to an MT 920 Request Message, this field must contain the field 20 Transaction Reference Number of the request message.
     * @validation This field must not start or end with a slash '/' and must not contain two consecutive slashes '//' (Error code(s): T26).
     */
    const TAG_21_RELATED_REFERENCE = ":21:";

    /**
     * Tag 25a: Account identification number. Mandatory field.
     *
     * @format 35x (standard) or 35x<br>4!a2!a2!c[3!c] (25P-format)
     * @multiplicity 1..1
     * @definition This field identifies the account and optionally the identifier code of the account owner for which the statement is sent.
     * @validation Identifier Code must be a registered BIC (Error code(s): T27, T28, T29, T45).
     * @usage Option P must only be used if the Receiver of the message is not the account owner.
     */
    const TAG_25a_ACCOUNT_IDENTIFICATION = ":25:";

    /**
     *
     */
    const TAG_28C_STATEMENT_SEQ_NUMBER = ":28C:";

    /**
     *
     */
    const TAG_60a_OPENING_BALANCE = ":60F:";

    /**
     * Tag 61: Statement line for individual transaction.
     *
     * This field contains the details of each transaction:
     *  - Subfield 1, Value Date, is the date on which the debit/credit is effective.
     *  - Subfield 2, Entry Date, is the date on which the transaction is booked to the account.
     *  - Subfield 3, Debit/Credit Mark, see description in the Codes section.
     *  - Subfield 4, Funds Code, is the 3rd character of the currency code, if needed.
     *  - Subfield 5, Amount, is the amount of the transaction.
     *  - Subfield 6, Transaction Type and Identification Code, see description in the Codes section.
     *  - Subfield 7, Reference for the Account Owner, is the reference of the message (SWIFT or any other) or document that resulted in this entry. This is a reference that the account owner can use to identify the reason for the entry. See further details in the Usage Rules section.
     *  - Subfield 8, Reference of the Account Servicing Institution, is the reference of the advice or instruction sent by the account servicing institution to the account owner. See further details in the Usage Rules section.
     *  - Subfield 9, Supplementary Details, see details in the Usage Rules section.
     *
     * Validated rules:
     *  - Subfield 1, Value Date, must be a valid date expressed as YYMMDD (Error code(s): T50).
     *  - The SWIFT System validates subfield 2, Entry Date (Date in reduced ISO form), using current System Year
     *    (Error code(s): T50).
     *  - The integer part of subfield 5, Amount, must contain at least one digit. The decimal comma ',' is mandatory
     *    and is included in the maximum length (Error code(s): T40, T43).
     *
     * @format YYMMDD[MMDD]2a[1!a]15d1!a3!c16x[//16x][<br>34x]
     * @multiplicity 0..1
     */
    const TAG_61_STATEMENT_LINE = ":61:";

    /**
     *
     */
    const TAG_62a_CLOSING_BALANCE_BOOKED = ":62F:";

    /**
     *
     */
    const TAG_64_CLOSING_BALANCE_AVAILABLE = ":64:";

    /**
     *
     */
    const TAG_65_CLOSING_BALANCE_FORWARD_AVAILABLE = ":65:";

    /**
     *
     */
    const TAG_86_ACCOUNT_OWNER_INFORMATION = ":86:";

    /**
     * Tag data.
     *
     * @var array
     */
    protected $tags;

    /**
     * MT940Formatter constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->tags = [];
    }

    /**
     * @inheritdoc
     */
    public function formatToString(): string
    {
        $output = "";

        // Account info
        $output .= $this->formatTagString(self::TAG_20_TRANSACTION_REFERENCE, $this->transactionReferenceNumber);
        $output .= $this->formatTagString(self::TAG_21_RELATED_REFERENCE, $this->relatedReference, false);
        $output .= $this->formatTagString(self::TAG_25a_ACCOUNT_IDENTIFICATION, $this->accountIdentification);
        $output .= $this->formatTagString(self::TAG_28C_STATEMENT_SEQ_NUMBER, $this->statementSeqNumber, true, "00000");

        // Opening balance
        $output .= $this->formatTagString(self::TAG_60a_OPENING_BALANCE, $this->openingBalance ?? null);

        // Statement lines
        foreach ($this->transactions as $transaction) {
            $output .= $this->formatTagString(self::TAG_61_STATEMENT_LINE, $transaction);

            if ($transaction->relatedAccountOwnerInfo) {
                $output .= $this->formatTagString(self::TAG_86_ACCOUNT_OWNER_INFORMATION, $transaction->relatedAccountOwnerInfo);
            }
        }

        // Closing balance
        $output .= $this->formatTagString(self::TAG_62a_CLOSING_BALANCE_BOOKED, $this->closingBalanceBooked ?? null);
        $output .= $this->formatTagString(self::TAG_64_CLOSING_BALANCE_AVAILABLE, $this->closingBalanceAvailable ?? null);

        // Forward available balances
        foreach ($this->forwardAvailableBalances as $fab) {
            $output .= $this->formatTagString(self::TAG_65_CLOSING_BALANCE_FORWARD_AVAILABLE, $fab);
        }

        // Information to account owner
        $output .= $this->formatTagString(self::TAG_86_ACCOUNT_OWNER_INFORMATION, $this->accountOwnerInfo, false);

        return $output;
    }

    /**
     * Formats a MT940 tag.
     *
     * @param string $tag Tag name. Usually in ":tag:" format.
     * @param string $tagValue Raw tag value.
     * @param bool $mandatory If true, always include this line. If false, blank output if value is empty.
     * @param string|null $tagValueDefault If mandatory field, use this as default field value if actual value is blank.
     * @return string
     * @example :28C:00000<CR/LF>
     */
    protected function formatTagString(string $tag, ?string $tagValue = null, bool $mandatory = true, ?string $tagValueDefault = null): string
    {
        if (empty($tagValue)) {
            if ($mandatory) {
                if (!empty($tagValueDefault)) {
                    // Blank field, mandatory, fall back to default value
                    $tagValue = $tagValueDefault;
                } else {
                    // Blank field, mandatory, no default available, cannot proceed
                    throw new FormatterException("Error formatting MT940 tag `{$tag}`. Field is mandatory, but no value provided, and no default value available. Ensure all required fields have a non-blank value.");
                }
            } else {
                // Blank field, not mandatory, return blank
                return "";
            }
        }

        return "{$tag}{$tagValue}" . self::CS2_EOL;
    }
}