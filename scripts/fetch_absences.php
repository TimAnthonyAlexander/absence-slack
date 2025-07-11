<?php
require __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL & ~E_DEPRECATED);

use App\Command\FetchAbsencesCommand;

// Execute the command
$command = new FetchAbsencesCommand($argv, __DIR__ . '/..');
$command->execute();
