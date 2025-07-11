<?php
require __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL & ~E_DEPRECATED);

use App\Command\FetchAbsencesCommand;

// Calculate dates for next Monday through Sunday
$nextMonday = new DateTime('next monday -1 day');
$nextSunday = new DateTime('next monday +6 days');

// Format dates as Y-m-d
$startDate = $nextMonday->format('Y-m-d');
$endDate = $nextSunday->format('Y-m-d');

// Execute the command
$command = new FetchAbsencesCommand([
    'fetch_absences.php', // Script name
    $startDate, // Start date (next Monday)
    $endDate    // End date (next Sunday)
], __DIR__ . '/..');
$command->execute();
