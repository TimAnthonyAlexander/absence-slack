<?php
require __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL & ~E_DEPRECATED);

ini_set('memory_limit', '512M');

use App\Command\CheckVacationDaysCommand;

// Execute the command
$command = new CheckVacationDaysCommand($argv, __DIR__ . '/..');
$command->execute(); 