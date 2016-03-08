<?php

namespace SoftwarePunt\IngCard\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConvertCommand extends Command
{
    protected function configure()
    {
        $this->setName('convert')
            ->setDescription('Convert a ingbanknetservice CSV file to a MT940 file')
            ->addArgument(
                'filename',
                InputArgument::OPTIONAL,
                'The file to read and covert, relative to the current working directory'
            )
            ->addArgument(
                'iban',
                InputArgument::OPTIONAL,
                'A custom account number to use (IBAN), defaults to NL24INGB0001111111 otherwise'
            );
    }

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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Determine path
        $filename = $input->getArgument('filename');

        if (empty($filename))
        {
            $filename = 'Transacties.TXT';
        }

        // Read file
        $output->writeln('Parsing file: ' . $filename . '...');

        if (!file_exists($filename))
        {
            throw new \Exception('File not found: ' . $filename);
        }

        $parsed = array_map('str_getcsv', file($filename));
        array_shift($parsed); // remove header

        if (!is_array($parsed) || count($parsed) <= 1)
        {
            throw new \Exception('Not a valid CSV file, or file does not contain any records: ' . $filename);
        }

        $output->writeln('Discovered ' . count($parsed). ' transactions in file.');

        // Determine file period
        $dateFromTs = PHP_INT_MAX;
        $dateToTs = -PHP_INT_MAX;

        foreach ($parsed as $transaction)
        {
            $trxDate = $transaction[self::C_DATE_OCCURED];
            $trxTs = strtotime($trxDate);

            if ($trxTs < $dateFromTs)
            {
                $dateFromTs = $trxTs;
            }

            if ($trxTs > $dateToTs)
            {
                $dateToTs = $trxTs;
            }
        }

        $output->writeln(sprintf('Transaction period reflected in file: %s - %s.', date('d-m-Y', $dateFromTs), date('d-m-Y', $dateToTs)));

        $output->writeln('');
        $output->writeln('Starting conversion to MT940 file format (ING version)...');

        // Begin: ING-specific headers
        $export = '';
        $export .= '{1:F01INGBNL2ABXXX0000000000}' . PHP_EOL;
        $export .= '{2:I940INGBNL2AXXXN}' . PHP_EOL;
        $export .= '{4:' . PHP_EOL;

        // START MT940 MESSAGE
        $iban = $input->getArgument('iban');

        if (empty($iban))
        {
            $iban = 'NL24INGB0001111111';
        }

        $export .= sprintf(':20:%s', uniqid('SPC')) . PHP_EOL;                                                              // Transaction reference, unique per file
        $export .= sprintf(':25:%s%s', $iban, 'EUR') . PHP_EOL;                                                             // Account number + currency code
        $export .= sprintf(':28C:%s', '00000') . PHP_EOL;                                                                   // Document number, unused even by ING
        $export .= sprintf(':60F:%s%s%s%s', 'C', date('ymd', $dateFromTs), 'EUR', '0,00') . PHP_EOL;                             // Starting balance Credit-Date-Currency-Amount

        $debitTrxCount = 0;
        $debitAmountTotal = 0;

        foreach ($parsed as $trx)
        {
            $debitTrxCount++;

            $transactionTs = strtotime($trx[self::C_DATE_OCCURED]);
            $bookedTs = strtotime($trx[self::C_DATE_BOOKED]);
            $trxAmount = $trx[self::C_AMOUNT_INVOICED_EUROS] . ',' . $trx[self::C_AMOUNT_INVOICED_CENTS];
            $recipient = $trx[self::C_SUPPLIER_NAME];

            $debitAmountTotal += floatval($trxAmount);

            $output->writeln(sprintf(' -> Payment to %s on %s: €%s', $recipient, date('m-d-Y', $transactionTs), $trxAmount));

            // 1. Transaction line
            $export .= ':61:';                                              // Transaction start tag
            $export .= date('ymd', $transactionTs);                         // Transaction date YYMMDD
            $export .= date('md', $bookedTs);                               // Booked date MMDD
            $export .= 'D';                                                 // Credit/debit
            $export .= $trxAmount;                                          // Transaction amount
            $export .= 'N' . 'TRF';                                         // Swift code (N) for TRANSFER (TRF)
            $export .= 'EREF';                                              // Payment reference (betalingskenmerk)
            $export .= '//' . substr($trx[self::C_REFERENCE], 0, 16);       // ING TRX Ref
            $export .= PHP_EOL;
            $export .= '/TRCD/00100/';                                      // ING Transaction Code (00100=SEPA Credit Transfer)
            $export .= PHP_EOL;

            // 2. Transaction details
            $export .= ':86:';
            $export .= sprintf('/EREF/%s/', $trx[self::C_REFERENCE]);       // Payment ref
            $export .= sprintf('/CNTP/%s/', $trx[self::C_SUPPLIER_NAME]);   // Counterparty
            $export .= PHP_EOL;
        }

        $export .= sprintf(':62F:%s%s%s%s', 'C', date('ymd', $dateToTs), 'EUR', '0,00') . PHP_EOL;                            // Final balance Credit-Date-Currency-Amount
        $export .= sprintf(':64:%s%s%s%s', 'C', date('ymd', $dateToTs), 'EUR', '0,00') . PHP_EOL;                             // Actual Final balance Credit-Date-Currency-Amount
        $export .= sprintf(':65:%s%s%s%s', 'C', date('ymd', $dateToTs), 'EUR', '0,00') . PHP_EOL;                             // Future balance Credit-Date-Currency-Amount

        // END: Message closure
        $export .= '-}';

        $outFile = sprintf('export_ing_card_%s.940', date('ymd', $dateFromTs));
        file_put_contents($outFile, $export);

        $actualFile = getcwd() . '/' . $outFile;

        $output->writeln('');
        $output->writeln("<info>Wrote export file: {$actualFile}</info>");
    }
}