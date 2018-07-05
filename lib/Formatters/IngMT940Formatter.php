<?php

namespace MT940\Formatters;

/**
 * Formatter for MT940, with ING-specific messaging format.
 * The ING standard is based on SWIFT FIN-standard Category 9, Cash Management and Customer Status.
 *
 * @see https://www.ing.nl/media/ING-Format-Description-MT940-MT942-Structured-NL-v5.4_tcm162-105412.pdf (EN)
 * @see https://www.ing.nl/media/266331_0616%20Mijn%20ING%20Zakelijk%20MT940_tcm162-147668.pdf (NL)
 */
class IngMT940Formatter extends MT940Formatter
{
    /**
     * @inheritdoc
     */
    public function getId(): string
    {
        return "mt940-ing";
    }

    /**
     * @inheritdoc
     */
    public function formatToString(): string
    {
        /**
         * Standard SWIFT message header and trailer is used.
         *
         * The message header contains up to 5 blocks:
         *  • Block 1: Export information
         *  • Block 2: Import information
         *  • Block 3: Optional message information1
         *  • Block 4: MT940 and MT942 message
         *  • Block 5: Message trailer1
         *
         * Format:
         *  {1 : <Export Info> }{2 : <Import Info> }{3 : <Optional message Info>}
         *  {4 : <MT940/MT942 statement>- }{5 :{CHK :141001456789}}
         *
         * (In practise, exports from ING NL always look the same, so we define this statically.)
         */

        $innerMt940 = parent::formatToString();

        $output  = "{1:F01INGBNL2ABXXX0000000000}" . self::CS2_EOL;
        $output .= "{2:I940INGBNL2AXXXN}"          . self::CS2_EOL;
        $output .= "{4:"                           . self::CS2_EOL;
        $output .= $innerMt940;
        $output .= "-}";

        return $output;
    }
}