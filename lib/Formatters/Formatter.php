<?php

namespace MT940\Formatters;

use MT940\Structs\Balance;
use MT940\Structs\Transaction;

/**
 * Base class for all Formatter implementations.
 */
abstract class Formatter
{
    /**
     * @var Transaction[]
     */
    protected $transactions;

    /**
     * 16-character transaction reference number. Required.
     *
     * @var string|null
     */
    protected $transactionReferenceNumber;

    /**
     * 35-character account identification number. Required.
     *
     * @var string
     */
    protected $accountIdentification;

    /**
     * 16-character transaction related reference. Optional.
     *
     * @var string|null
     */
    protected $relatedReference;

    /**
     * 5-character statement number / sequence number. Required, but can be defaulted to "00000".
     *
     * @var string|null
     */
    protected $statementSeqNumber;

    /**
     * Opening balance. Required.
     *
     * @var Balance|null
     */
    protected $openingBalance;

    /**
     * (Intermediate) closing balance (booked funds). Required.
     *
     * @var Balance|null
     */
    protected $closingBalanceBooked;

    /**
     * Closing balance (available funds). Required.
     *
     * @var Balance|null
     */
    protected $closingBalanceAvailable;

    /**
     * List of known forward available balances. Optional.
     *
     * @var Balance[]
     */
    protected $forwardAvailableBalances;

    /**
     * Additional info for the account owner. Optional.
     *
     * @var string
     */
    protected $accountOwnerInfo;

    /**
     * Formatter constructor.
     */
    public function __construct()
    {
        $this->transactions = [];
        $this->forwardAvailableBalances = [];
    }

    /**
     * Returns a unique identifier / reference for this formatter.
     *
     * @return string
     */
    public abstract function getId(): string;

    /**
     * @param string $trn
     */
    public function setTransactionReferenceNumber(string $trn): void
    {
        $this->transactionReferenceNumber = $trn;
    }

    /**
     * @param string $accountIdent
     */
    public function setAccountIdentification(string $accountIdent): void
    {
        $this->accountIdentification = $accountIdent;
    }

    /**
     * @param null|string $relatedReference
     */
    public function setRelatedReference(?string $relatedReference): void
    {
        $this->relatedReference = $relatedReference;
    }

    /**
     * @param string $statementSeqNumber
     */
    public function setStatementSeqNumber(string $statementSeqNumber = "00000"): void
    {
        $this->statementSeqNumber = $statementSeqNumber;
    }

    /**
     * @param Balance $balance
     */
    public function setOpeningBalance(Balance $balance)
    {
        $this->openingBalance = $balance;
    }

    /**
     * @param Balance $balance
     */
    public function setClosingBalanceBooked(Balance $balance): void
    {
        $this->closingBalanceBooked = $balance;
    }

    /**
     * @param Balance $balance
     */
    public function setClosingBalanceAvailable(Balance $balance): void
    {
        $this->closingBalanceAvailable = $balance;
    }

    /**
     * @param Balance $fab
     */
    public function addForwardAvailableBalance(Balance $fab): void
    {
        $this->forwardAvailableBalances[] = $fab;
    }

    /**
     * @param null|string $accountOwnerInfo
     */
    public function setAccountOwnerInfo(?string $accountOwnerInfo): void
    {
        $this->accountOwnerInfo = $accountOwnerInfo;
    }

    /**
     * @param Transaction $transaction
     */
    public function addTransaction(Transaction $transaction): void
    {
        $this->transactions[] = $transaction;
    }

    /**
     * Formats to a string.
     *
     * @return string
     */
    public abstract function formatToString(): string;

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return $this->formatToString();
    }
}