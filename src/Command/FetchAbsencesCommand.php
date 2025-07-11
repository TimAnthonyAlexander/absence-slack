<?php

namespace App\Command;

use App\Api\AbsenceClient;
use App\Cli\Arguments;
use App\Config\Config;
use App\Data\AbsenceProcessor;
use App\Notification\SlackNotifier;

class FetchAbsencesCommand
{
    private Config $config;
    private Arguments $args;
    private AbsenceClient $client;
    private AbsenceProcessor $processor;
    private ?SlackNotifier $slackNotifier = null;

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

        // Initialize Slack notifier if enabled
        if ($this->config->isSlackEnabled()) {
            $this->slackNotifier = new SlackNotifier(
                $this->config->get('botToken'),
                $this->config->get('slackChannelId'),
            );
        }
    }

    public function execute(): void
    {
        $start = $this->args->getStartDate();
        $end = $this->args->getEndDate();

        $firstName = $this->args->getFirstName();
        $lastName = $this->args->getLastName();

        $allowedNames = $this->config->getAllowedNames();
        if ($firstName && $lastName) {
            $fullName = $firstName . ' ' . $lastName;
            if (!in_array($fullName, $allowedNames)) {
                $allowedNames[] = $fullName;
            }
        }
        $this->processor = new AbsenceProcessor($allowedNames, $this->config->get('filterReasonId'));

        // Fetch absences
        if ($firstName && $lastName) {
            $absences = $this->client->fetchAbsencesByName($firstName, $lastName, $start, $end);
        } else {
            $absences = $this->client->fetchAbsences($start, $end);
        }

        // Process and filter the data
        $processedAbsences = $this->processor->processAbsences($absences);

        // Output the results
        $this->outputResults($processedAbsences, $start, $end);

        // Send to Slack if enabled
        if ($this->slackNotifier) {
            $this->sendToSlack($processedAbsences, $start, $end);
        }
    }

    private function outputResults(array $processedAbsences, string $startDate, string $endDate): void
    {
        // Define ANSI color codes for prettier output
        $colors = [
            'reset' => "\033[0m",
            'bold' => "\033[1m",
            'green' => "\033[32m",
            'yellow' => "\033[33m",
            'blue' => "\033[34m",
            'magenta' => "\033[35m",
            'cyan' => "\033[36m",
            'white' => "\033[37m",
            'bg_blue' => "\033[44m",
        ];

        // Calculate terminal width (default to 80 if not detectable)
        $termWidth = (int) (`tput cols` ?? 80);

        // Print header with date range
        $startDateObj = new \DateTime($startDate);
        $endDateObj = new \DateTime($endDate);
        $headerText = " Absences from " . $startDateObj->format('d.m.Y') . " to " . $endDateObj->format('d.m.Y') . " ";
        if ($this->args->getFirstName() && $this->args->getLastName()) {
            $headerText = " Absences for " . $this->args->getFirstName() . " " . $this->args->getLastName() . $headerText;
        }
        $padding = str_repeat('═', (int)(($termWidth - strlen($headerText)) / 2));

        echo PHP_EOL;
        echo "{$colors['bold']}{$colors['bg_blue']}{$colors['white']}{$padding}{$headerText}{$padding}{$colors['reset']}" . PHP_EOL . PHP_EOL;

        if (empty($processedAbsences)) {
            echo "  {$colors['yellow']}No absences found for this period.{$colors['reset']}" . PHP_EOL . PHP_EOL;
        } else {
            // Group absences by person
            $absencesByPerson = [];
            foreach ($processedAbsences as $absence) {
                $personName = $absence['name'];
                if (!isset($absencesByPerson[$personName])) {
                    $absencesByPerson[$personName] = [];
                }
                $absencesByPerson[$personName][] = $absence;
            }

            // Display absences grouped by person
            foreach ($absencesByPerson as $personName => $absences) {
                echo "  {$colors['cyan']}■ {$colors['bold']}{$personName}{$colors['reset']}" . PHP_EOL;

                foreach ($absences as $absence) {
                    $absenceText = $this->processor->formatAbsence($absence, $startDate, $endDate);
                    $absenceText = str_replace($personName . ": ", "", $absenceText); // Remove redundant name

                    // Highlight important information
                    $absenceText = preg_replace('/(\d+\.\d+\.\d+)/', "{$colors['green']}$1{$colors['reset']}", $absenceText);
                    $absenceText = preg_replace('/(\d+ Tage)/', "{$colors['yellow']}$1{$colors['reset']}", $absenceText);

                    echo "    {$colors['white']}▪ {$absenceText}{$colors['reset']}" . PHP_EOL;
                }
                echo PHP_EOL;
            }
        }

        // Print summary footer
        $footerText = " Total absences: " . count($processedAbsences) . " ";
        $footerPadding = str_repeat('─', $termWidth - strlen($footerText) - 2);
        echo "{$colors['bold']}{$footerPadding} {$colors['magenta']}{$footerText}{$colors['reset']}" . PHP_EOL . PHP_EOL;
    }

    private function sendToSlack(array $processedAbsences, string $startDate, string $endDate): void
    {
        if (!$this->slackNotifier) {
            return;
        }

        // Format message for Slack
        $message = $this->slackNotifier->formatAbsencesMessage($processedAbsences, $startDate, $endDate);

        // Send the message
        $success = $this->slackNotifier->sendMessage($message);

        if ($success) {
            echo "Successfully sent absence information to Slack channel {$this->config->get('slackChannelId')}." . PHP_EOL;
        } else {
            echo "Failed to send absence information to Slack." . PHP_EOL;
        }
    }
}
