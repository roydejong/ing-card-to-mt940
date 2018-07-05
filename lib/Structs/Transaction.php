<?php

namespace MT940\Structs;

use MT940\Formatters\MT940Formatter;
use MT940\Structs\StructDataException;

class Transaction
{
    /**
     * For entries being first advised by the statement (items originated by the account servicing institution).
     */
    const TYPE_FIRST_ADVICE = "F";
    /**
     * For entries related to payment and transfer instructions, including related charges messages, not sent through SWIFT or where an alpha description is preferred.
     */
    const TYPE_NON_SWIFT_TRANSFER = "N";
    /**
     * For entries related to SWIFT transfer instructions and subsequent charge messages.
     */
    const TYPE_SWIFT_TRANSFER = "S";

    /**
     * The date on which the debit/credit is effective. Required.
     *
     * @default null
     * @var \DateTime
     */
    public $valueDate = null;

    /**
     * The date on which the transaction is booked to the account. Optional.
     * Entry Date is normally only used when different from the value date.
     * The SWIFT System validates subfield 2, Entry Date (Date in reduced ISO form), using current System Year.
     *
     * @default null
     * @var \DateTime|null
     */
    public $entryDate = null;

    /**
     * Debit/Credit Mark
     *
     * @default false
     * @var bool
     */
    public $isCredit = false;

    /**
     * The 3rd character of the currency code, if needed. Optional.
     * If set to a string longer than one character, the last character will be used.
     *
     * @default null
     * @var string|null
     */
    public $fundsCode = null;

    /**
     * Transaction amount.
     *
     * @var float
     */
    public $amount = 0;

    /**
     * Transaction type, required:
     * `F` (First Advice), `N` (NonSwiftTransfer) or `S` (SwiftTransfer)
     *
     * @default N
     * @var string
     */
    public $type = self::TYPE_NON_SWIFT_TRANSFER;

    /**
     * Transaction code, to indicate the type of the transaction, 3 characters. Required.
     * Closely related to the transaction type (F, N or S) which acts as a prefix to the type code.
     *
     * @default TRF (Transfer)
     * @var string
     */
    public $typeIdentCode = "TRF";

    /**
     * Reference for the Account Owner, is the reference of the message (SWIFT or any other) or document that resulted
     * in this entry. This is a reference that the account owner can use to identify the reason for the entry.
     *
     * This field is required.
     *
     * Typically used as a payment reference.
     *
     * May be set to "NONREF" if no reference is available for the transaction.
     *
     * @default NONREF
     * @var string
     */
    public $referenceCustomer = "NONREF";

    /**
     * Reference of the Account Servicing Institution, is the reference of the advice or instruction sent by the account
     * servicing institution to the account owner.
     *
     * The content of this subfield is the account servicing institution's own reference for the transaction.
     *
     * When the transaction has been initiated by the account servicing institution, this reference may be identical
     * to subfield 7, Reference for the Account Owner. If this is the case, Reference of the Account Servicing
     * Institution, subfield 8 may be omitted.
     *
     * Value is automatically prefixed with "//", leaving 14 characters to be filled.
     *
     * @default (blank)
     * @var string
     */
    public $referenceInstitution = "";

    /**
     * 34 characters for supplementary details. Optional.
     *
     * ING uses this field for custom codes, e.g. "/TRCD/<ING transaction code>/".
     *
     * - If the customer reference is "NONREF", this field SHOULD contain the best available alternative reference.
     * - Supplementary details may be provided when an advice has not been sent for a transaction, or to provide
     *   additional information to facilitate reconciliation.
     * - This field may contain ERI to transport dual currencies, as explained in the chapter
     *   "Euro-Related Information (ERI)" in the Standards MT General Information.
     * - In order to comply with the EC-directive on cross border credit transfers, the optional code word EXCH may be
     *   used to transport an exchange rate. In line with ERI, the code word EXCH is placed between slashes, followed
     *   by the exchange rate, format 12d, and terminated with another slash.
     *
     * @var string
     */
    public $supplementaryDetails;

    /**
     * If set, an additional "account owner info" tag will be included in the MT940 export for this transaction.
     *
     * This field contains additional information about the transaction detailed in the preceding statement line and
     * which is to be passed on to the account owner.
     *
     * NB: This field is not included in Transaction::__toString() output, as it is not part of the transaction line.
     *
     * This field may contain ERI to transport dual currencies, as explained in the chapter "Euro-Related Information
     * (ERI)" in the Standards MT General Information.
     *
     * Since the charges field in the customer transfers is repetitive, it may be necessary to report more than one
     * charges amount in the resulting statement. In this case, it is allowed to repeat the code word CHGS before the
     * code word OCMT. The order in which the charges are specified is the same as in the customer transfers, that is,
     * the order in which the charges have been taken during the transaction. So, the last appearance of the code word
     * CHGS always specifies the charges (if any) of the account servicing institution.
     *
     * In order to comply with the EC-directive on cross border credit transfers, the optional code word EXCH may be
     * used to transport an exchange rate. In line with ERI, the code word EXCH is placed between slashes, followed by
     * the exchange rate, format 12d, and terminated with another slash. The code may be repeated if the account
     * servicing institution wants to report an exchange rate that it applied, in addition to the exchange rate
     * received in the instruction. The order in which the exchange rates are specified is the same as the order in
     * which the rates have been applied during the transaction. So, the last appearance of the code word EXCH always
     * specifies the rate applied by the account servicing institution.
     *
     * An ordering party is identified with the preceding code /ORDP/. The information following this code is copied
     * from field 50a of the customer payment order, or field 52a (sender if field 52a is not present) of the financial
     * institution transfer. The code should be used at the beginning of a line.
     *
     * In case of a debit item, a beneficiary party may be identified with the preceding code /BENM/. The information
     * following this code is copied from field 59a of the customer payment order, or field 58a of the financial
     * institution transfer. The code should be used at the beginning of a line.
     *
     * In case remittance information from field 70 of the payment instruction is to be included in this field, it
     * should be preceded by the code /REMI/.
     *
     * In case the information in field 72 of the payment instruction is intended for the account owner, it should be
     * copied into field 86 as it is. Codes used in field 72 of the payment instruction have therefore the same meaning
     * in field 86 of the statement. If only free text is used in field 72, it is to be copied as it is since a code in
     * field 86 will not add any value.
     *
     * @var string
     */
    public $relatedAccountOwnerInfo;

    /**
     * Transaction constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * @throws \MT940\Structs\StructDataException
     * @return string
     */
    public function __toString(): string
    {
        /**
         * @format YYMMDD[MMDD]2a[1!a]15d1!a3!c16x[//16x][<br>34x]
         * @multiplicity 0..1
         */

        $output = "";

        // Subfield 1, Value Date, is the date on which the debit/credit is effective => YYMMDD
        // Subfield 1, Value Date, must be a valid date expressed as YYMMDD (Error code(s): T50).
        $output .= $this->valueDate->format("ymd");

        // Subfield 2, Entry Date, is the date on which the transaction is booked to the account => [MMDD]
        if ($this->entryDate) {
            $output .= $this->entryDate->format("md");

            if ($this->entryDate->format('y') !== date('y')) {
                throw new StructDataException("Date error: Entry Date must be refer to the current system year, as no other years can be expressed."  . PHP_EOL
                    . "(The SWIFT System validates subfield 2, Entry Date using current System Year, error code(s): T50.)");
            }
        }

        // Subfield 3, Debit/Credit Mark, see description in the Codes section => 2a
        // Possible values: {C, D, RC, RD} (R prefix indicates a reversal/refund)
        $output .= $this->isCredit ? "C" : "D";

        // Subfield 4, Funds Code, is the 3rd character of the currency code, if needed.
        if ($this->fundsCode) {
            $output .= substr($this->fundsCode, -1, 1);
        }

        // Subfield 5, Amount, is the amount of the transaction.
        // The integer part of subfield 5, Amount, must contain at least one digit.
        // The decimal comma ',' is mandatory and is included in the maximum length (Error code(s): T40, T43).
        $output .= number_format($this->amount, 2, ',', '');

        // Subfield 6, Transaction Type and Identification Code, see description in the Codes section.
        $output .= $this->type;
        $output .= $this->typeIdentCode;

        // Subfield 7/8, References
        if (empty(trim($this->referenceCustomer))) {
            throw new StructDataException("Reference for the Account Owner: At least one valid character other than a blank must be present.");
        }

        $output .= $this->referenceCustomer;

        if ($this->referenceInstitution) {
            $output .= "//{$this->referenceInstitution}";
        }

        // Subfield 9, Supplementary Details, see details in the Usage Rules section.
        if ($this->supplementaryDetails) {
            $output .= MT940Formatter::CS2_EOL;
            $output .= $this->supplementaryDetails;
        }

        return $output;
    }
}