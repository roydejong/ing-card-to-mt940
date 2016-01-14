#!/usr/bin/env php
<?php

require_once 'vendor/autoload.php';

use SoftwarePunt\IngCard\Commands\ConvertCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new ConvertCommand());
$application->run();