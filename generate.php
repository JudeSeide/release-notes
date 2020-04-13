#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

use App\Command;
use Dotenv\Dotenv;
use Symfony\Component\Console\Application;

$application = new Application();


$dotenv = Dotenv::createMutable(__DIR__);
$dotenv->load();

$application->add(new Command());

$application->run();