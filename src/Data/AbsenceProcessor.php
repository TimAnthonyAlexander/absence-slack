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
        $requestStart = new \DateTime($requestStart);
        $requestEnd = new \DateTime($requestEnd);
        $startDate = new \DateTime($absence['start']);
        $endDate = new \DateTime($absence['end']);
        $startDateFormatted = $startDate->format('d.m.Y');
        $endDateFormatted = $endDate->modify('-1 day')->format('d.m.Y');

        $return = "{$absence['name']}: {$startDateFormatted} bis {$endDateFormatted}";

        if ($requestStart >= $startDate) {
            // Calculate how much is left til end date
            $endDate = new \DateTime($absence['end']);

            $interval = $requestStart->diff($endDate);
            if ($interval->invert === 0) {
                $return .= " - " . $interval->format('%a Tage Urlaub übrig');

                if ($interval->days >= 5) {
                    $return .= " (ganze nächste Woche)";
                }
            } else {
                $return .= " - ended " . $interval->format('%a Tage her');
            }
        } else {
            // Calculate how many days of the vacation are within the request period
            $vacationEndDate = new \DateTime($absence['end']);
            $overlapStart = $startDate; // Vacation starts after request start
            $overlapEnd = min($vacationEndDate, $requestEnd);
            
            if ($overlapEnd >= $overlapStart) {
                $interval = $overlapStart->diff($overlapEnd);
                $overlapDays = $interval->days;
                $return .= " - " . $overlapDays . " Tage im angefragten Zeitraum";
            } else {
                $return .= " - kein Überlapp mit angefragtem Zeitraum";
            }
        }

        return $return;
    }
}
