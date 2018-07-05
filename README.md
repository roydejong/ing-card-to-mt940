# mt940

**mt940 is a PHP-based library and command line tool that can parse transaction files and convert them to compatible MT940 formats.**

## Features

- Integrate as a PHP library, or install globally to use it as a command line tool.
- Convert to a variety of supported MT940 formats.
- Parse input from various file formats like ING Commercial Card CSV files.

### MT940 Format compatibility

The following MT940 formats are currently supported by this library:

|Identifier|Standard|Institution|Reference version|
|---|---|---|---|
|`mt940`|Standalone [MT 940 Customer Statement Message](https://www2.swift.com/mystandards/#/mt/2017.November/940/940!content)|SWIFT|SR 2017 (September 2017)|
|`mt940-ing`|SWIFT Message [MT940 / MT942 Structured NL](https://www.ing.nl/media/ING-Format-Description-MT940-MT942-Structured-NL-v5.4_tcm162-105412.pdf)|ING Bank Nederland|v5.4 (01-08-2016)|

## Installation 

To install as a **library** for integration in to your project, add it as a dependency:

    composer require roydejong/mt940
    
To install globally as a **command-line tool** on your system, install it as a global composer package:

    composer install --global roydejong/mt940

## Usage as a library



## Usage on the command line