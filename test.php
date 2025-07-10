<?php
require __DIR__ . '/vendor/autoload.php';

error_reporting(E_ALL & ~E_DEPRECATED);

use Dflydev\Hawk\Client\ClientBuilder;
use Dflydev\Hawk\Credentials\Credentials;
use GuzzleHttp\Client as Guzzle;
use Dotenv\Dotenv;

// Load environment variables from .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Get credentials from environment
$apiId  = $_ENV['API_ID'];
$apiKey = $_ENV['API_KEY'];
$teamId = $_ENV['TEAM_ID'];

// Handle command line arguments
$startDate = '2025-07-10';
$endDate = '2025-07-11';

if ($argc > 1) {
    if ($argv[1] === '--help' || $argv[1] === '-h') {
        echo "Usage: php test.php [start_date] [end_date]\n";
        echo "Dates should be in YYYY-MM-DD format\n";
        echo "Example: php test.php 2025-07-10 2025-07-15\n";
        exit(0);
    }
    
    $startDate = $argv[1];
    
    if ($argc > 2) {
        $endDate = $argv[2];
    }
}

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
    echo "Error: Dates should be in YYYY-MM-DD format\n";
    exit(1);
}

$payload = [
    'skip'   => 0,
    'limit'  => 500,
    'filter' => [
        'start' => ['$gte' => $startDate . 'T23:59:59.999Z'],
        'end'   => ['$lte' => $endDate . 'T00:00:00.000Z'],
    ],
    'relations' => ['assignedToId']
];
$body = json_encode($payload, JSON_UNESCAPED_SLASHES);

$creds  = new Credentials($apiKey, 'sha256', $apiId);
$hawk   = ClientBuilder::create()->build();
$header = $hawk->createRequest(
    $creds,
    'https://app.absence.io/api/v2/absences',
    'POST',
    ['payload' => $body, 'content_type' => 'application/json']
)
    ->header()->fieldValue();

$resp = (new Guzzle(['http_errors' => false]))->post(
    'https://app.absence.io/api/v2/absences',
    [
        'headers' => [
            'Content-Type'  => 'application/json',
            'Authorization' => $header
        ],
        'body' => $body
    ]
);

$data = json_decode($resp->getBody(), true)['data'];

foreach ($data as $entry) {
    if (!isset($entry['assignedTo'])) continue;
    $name = $entry['assignedTo']['firstName'] . ' ' . $entry['assignedTo']['lastName'];
    $start = substr($entry['start'], 0, 10);
    $end = substr($entry['end'], 0, 10);
    $days = $entry['daysCount'];
    echo "{$name}: {$start} to {$end} ({$days} days)\n";
}
