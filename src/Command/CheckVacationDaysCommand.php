<?php

namespace App\Command;

use App\Api\AbsenceClient;
use App\Cli\Arguments;
use App\Config\Config;

class CheckVacationDaysCommand
{
    private Config $config;
    private Arguments $args;
    private AbsenceClient $client;

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
    }

    public function execute(): void
    {
        $start = $this->args->getStartDate();
        $end = $this->args->getEndDate();
        $allowedNames = $this->config->getAllowedNames();

        $vacationDays = [];

        foreach ($allowedNames as $fullName) {
            $lastSpace = strrpos($fullName, ' ');
            if ($lastSpace === false) {
                $firstName = $fullName;
                $lastName = '';
            } else {
                $firstName = substr($fullName, 0, $lastSpace);
                $lastName = substr($fullName, $lastSpace + 1);
            }

            // Fetch at least one absence to get the person's data
            $absences = $this->client->fetchAbsencesByName($firstName, $lastName, $start, $end);

            if (!empty($absences)) {
                // Get the first entry as it contains the allowance info
                $absence = $absences[0];

                if (isset($absence['assignedTo']['allowanceInfo'])) {
                    $totalAllowance = 0;

                    // Sum up all initialAllowance values
                    foreach ($absence['assignedTo']['allowanceInfo'] as $allowanceInfo) {
                        if (isset($allowanceInfo['initialAllowance'])) {
                            $totalAllowance += $allowanceInfo['initialAllowance'];
                        }
                    }

                    $vacationDays[$fullName] = $totalAllowance;
                }
            }
        }

        $this->outputResults($vacationDays);
    }

    private function outputResults(array $vacationDays): void
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

        // Print header
        $headerText = " Vacation Days Summary ";
        $padding = str_repeat('═', (int)(($termWidth - strlen($headerText)) / 2));

        echo PHP_EOL;
        echo "{$colors['bold']}{$colors['bg_blue']}{$colors['white']}{$padding}{$headerText}{$padding}{$colors['reset']}" . PHP_EOL . PHP_EOL;

        if (empty($vacationDays)) {
            echo "  {$colors['yellow']}No vacation data found.{$colors['reset']}" . PHP_EOL . PHP_EOL;
            return;
        }

        // Sort by number of vacation days (highest first)
        arsort($vacationDays);

        // Determine the person with the most days
        $maxDaysPerson = key($vacationDays);
        $maxDays = current($vacationDays);

        // Display all people and their vacation days
        foreach ($vacationDays as $person => $days) {
            $indicator = ($person === $maxDaysPerson) ? "★ " : "■ ";
            $nameColor = ($person === $maxDaysPerson) ? $colors['green'] : $colors['cyan'];

            echo "  {$nameColor}{$indicator}{$colors['bold']}{$person}{$colors['reset']}: ";
            echo "{$colors['yellow']}{$days} days{$colors['reset']}" . PHP_EOL;
        }

        echo PHP_EOL;

        // Print summary footer with the winner
        $footerText = " Most vacation days: {$maxDaysPerson} with {$maxDays} days ";
        $footerPadding = str_repeat('─', $termWidth - strlen($footerText) - 2);
        echo "{$colors['bold']}{$footerPadding} {$colors['magenta']}{$footerText}{$colors['reset']}" . PHP_EOL . PHP_EOL;
    }
}
