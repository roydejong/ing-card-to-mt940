ingbanknetservice CSV -> ING MT940
===

What?
---

**Convert a CSV file, exported from ING Banknet Service (ingbanknetservice.nl), to MT940 format.**

It currently converts the following information into the MT940 file:

- Transaction dates
- Transaction booking dates
- Transaction amounts
- Credit card transaction references
- Recipient (names only)

Why?
---
We needed MT940 format to import into our bookkeeping software (MoneyBird) to process bank mutations. ING does not currently support this for their business cards.

How?
---
If you have an ING Business Card, this utility can work for you.

**To export the CSV:**

1. If you haven't done so already, create an account on ingbanknetservice.nl
2. Sign in and select your card. Go to the "Transactions" tab.
3. Select a period, pick the comma-seperated (*Door komma's gescheiden tekst*) file format, and click "Download"
 
**To use this utility:**

1. Download or clone the repository contents
2. Run ``composer install`` from the terminal to install dependencies
3. Execute the command: `php convert.php convert [<filename>] [<iban>]`

**Parameters:**
All parameters are optional.

- ``filename``: A custom filename. Defaults to `Transacties.TXT`. Relative to the current working directory.
- ``iban``: A custom IBAN that will be used to identify your credit card, if useful to you. Defaults to `NL24INGB0001111111`.

Notes
---

- You'll need [composer](https://getcomposer.org/download/) and [php-cli](https://www.google.nl/search?q=install+php+cli) installed to use this tool.
- The exported MT940 is not perfect. Some fields are not correctly formatted (such as the counterparty data), and e.g. the final SUM is missing. But it works for our purposes.
- Both a starting and final balance of €0 is reported.
- [MT940 format specifications](https://www.ing.nl/media/ING_ming_mt940s_24_juli_tcm162-46356.pdf)
