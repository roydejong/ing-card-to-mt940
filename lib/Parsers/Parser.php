<?php

namespace MT940\Parsers;

/**
 * Base class for all Parser implementations.
 */
abstract class Parser
{
    /**
     * Returns a unique identifier / reference for this parser.
     *
     * @return string
     */
    public abstract function getId(): string;
}