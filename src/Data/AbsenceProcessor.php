<?php

namespace App\Data;

class AbsenceProcessor
{
    private array $allowedNames;
    private string $filterReasonId;

    public function __construct(array $allowedNames, string $filterReasonId)
    {
        $this->allowedNames = $allowedNames;
        $this->filterReasonId = $filterReasonId;
    }

    public function processAbsences(array $data): array
    {
        $processedAbsences = [];

        foreach ($data as $entry) {
            if (!isset($entry['assignedTo'])) {
                continue;
            }
            
            $name = $entry['assignedTo']['firstName'] . ' ' . $entry['assignedTo']['lastName'];
            
            // Skip if not in allowed names list
            if (!in_array($name, $this->allowedNames)) {
                continue;
            }

            // Skip if matches the filter reason ID
            if (($entry['reasonId'] ?? '') === $this->filterReasonId) {
                continue;
            }

            $processedEntry = [
                'name' => $name,
                'start' => substr($entry['start'], 0, 10),
                'end' => substr($entry['end'], 0, 10),
                'daysCount' => $entry['daysCount'],
            ];

            $processedAbsences[] = $processedEntry;
        }

        return $processedAbsences;
    }

    public function formatAbsence(array $absence): string
    {
        return "{$absence['name']}: {$absence['start']} to {$absence['end']} ({$absence['daysCount']} days)";
    }
} 