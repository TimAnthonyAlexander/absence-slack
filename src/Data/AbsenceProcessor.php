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

    public function formatAbsence(array $absence, string $requestStart, string $requestEnd): string
    {
        // Convert string dates to DateTime objects
        $requestStartDate = new \DateTime($requestStart);
        $requestEndDate = new \DateTime($requestEnd);
        $absenceStartDate = new \DateTime($absence['start']);
        $absenceEndDate = new \DateTime($absence['end']);
        
        // Format the date range for display (end date is exclusive)
        $displayStartDate = $absenceStartDate->format('d.m.Y');
        $displayEndDate = (clone $absenceEndDate)->modify('-1 day')->format('d.m.Y');
        
        // Basic absence information
        $result = sprintf("%s: %s bis %s", $absence['name'], $displayStartDate, $displayEndDate);
        
        if ($requestStartDate >= $absenceStartDate) {
            // Case: Absence has already started by request date
            // Calculate remaining vacation days
            $daysRemaining = $requestStartDate->diff($absenceEndDate);
            
            if ($daysRemaining->invert === 0) {
                // Absence is still ongoing
                $result .= sprintf(" - %s Tage Urlaub übrig", $daysRemaining->format('%a'));
                
                if ($daysRemaining->days >= 5) {
                    $result .= " (ganze nächste Woche)";
                }
            } else {
                // Absence has already ended
                $result .= sprintf(" - ended %s Tage her", $daysRemaining->format('%a'));
            }
        } else {
            // Case: Absence starts after request date
            // Calculate overlap between absence and request period
            $overlapStart = $absenceStartDate;
            $overlapEnd = min($absenceEndDate, $requestEndDate);
            
            if ($overlapEnd >= $overlapStart) {
                // There is an overlap
                $overlapDays = $overlapStart->diff($overlapEnd)->days;
                $result .= sprintf(" - %d Tage im angefragten Zeitraum", $overlapDays);
            } else {
                // No overlap
                $result .= " - kein Überlapp mit angefragtem Zeitraum";
            }
        }
        
        return $result;
    }
}
