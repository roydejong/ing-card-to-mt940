<?php

namespace MT940;
use MT940\Formatters\Formatter;
use MT940\Formatters\IngMT940Formatter;
use MT940\Formatters\MT940Formatter;
use MT940\Parsers\Parser;

/**
 * Global helper class for MT940 package.
 */
class MT940
{
    /**
     * @var Formatter[]
     */
    protected static $formatters;

    /**
     * @var Parser[]
     */
    protected static $parsers;

    /**
     * Self-initialization code; executed when the library is first referenced.
     */
    public static function bootstrap()
    {
        self::$formatters = [];
        self::$parsers = [];

        // Register built-in formatters
        self::registerFormatter(new MT940Formatter());
        self::registerFormatter(new IngMT940Formatter());

        // Register built-in parsers
    }

    /**
     * Registers a Formatter option.
     *
     * @param Formatter $formatter
     */
    public static function registerFormatter(Formatter $formatter)
    {
        self::$formatters[] = $formatter;
    }

    /**
     * Registers a Parser option.
     *
     * @param Parser $parser
     */
    public static function registerParser(Parser $parser)
    {
        self::$parsers[] = $parser;
    }
}

MT940::bootstrap();