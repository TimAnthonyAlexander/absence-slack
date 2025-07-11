<?php

namespace App\Command;

use App\Api\AbsenceClient;
use App\Cli\Arguments;
use App\Config\Config;
use App\Data\AbsenceProcessor;

class FetchAbsencesCommand
{
    private Config $config;
    private Arguments $args;
    private AbsenceClient $client;
    private AbsenceProcessor $processor;

    public function __construct(array $argv, string $rootDir)
    {
        // Initialize config
        $this->config = new Config($rootDir);

        // Parse command line arguments
        $this->args = new Arguments($argv);

        // Initialize API client
        $this->client = new AbsenceClient(
            $this->config->get('apiId'),
            $this->config->get('apiKey')
        );

        // Initialize data processor
        $this->processor = new AbsenceProcessor(
            $this->config->getAllowedNames(),
            $this->config->get('filterReasonId')
        );
    }

    public function execute(): void
    {
        // Fetch absences from the API
        $absences = $this->client->fetchAbsences(
            $this->args->getStartDate(),
            $this->args->getEndDate()
        );

        // Process and filter the data
        $processedAbsences = $this->processor->processAbsences($absences);

        // Output the results
        $this->outputResults($processedAbsences, $this->args->getStartDate(), $this->args->getEndDate());
    }

    private function outputResults(array $processedAbsences, string $startDate, string $endDate): void
    {
        foreach ($processedAbsences as $absence) {
            echo $this->processor->formatAbsence($absence, $startDate, $endDate) . PHP_EOL;
        }

        echo "Total absences: " . count($processedAbsences) . PHP_EOL;
    }
}

