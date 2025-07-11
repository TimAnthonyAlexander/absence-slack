<?php

namespace App\Cli;

class Arguments
{
    private array $args;
    private string $startDate;
    private string $endDate;

    public function __construct(array $args)
    {
        $this->args = $args;
        $this->parseArguments();
    }

    private function parseArguments(): void
    {
        // Default dates
        $this->startDate = date('Y-m-d');
        $this->endDate = date('Y-m-d');

        // Check for help flag
        if (isset($this->args[1]) && ($this->args[1] === '--help' || $this->args[1] === '-h')) {
            $this->showHelp();
            exit(0);
        }

        // Parse start date if provided
        if (isset($this->args[1])) {
            $this->startDate = $this->args[1];
        }

        // Parse end date if provided
        if (isset($this->args[2])) {
            $this->endDate = $this->args[2];
        }

        // Validate date formats
        $this->validateDates();
    }

    private function validateDates(): void
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->startDate) || 
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->endDate)) {
            echo "Error: Dates should be in YYYY-MM-DD format\n";
            exit(1);
        }
    }

    private function showHelp(): void
    {
        echo "Usage: php fetch_absences.php [start_date] [end_date]\n";
        echo "Dates should be in YYYY-MM-DD format\n";
        echo "Example: php fetch_absences.php 2025-07-10 2025-07-15\n";
    }

    public function getStartDate(): string
    {
        return $this->startDate;
    }

    public function getEndDate(): string
    {
        return $this->endDate;
    }
} 