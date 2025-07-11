<?php

namespace App\Notification;

class SlackNotifier
{
    public function __construct(
        private string $botToken,
        private string $channelId,
    ) {}

    public function sendMessage(string $message): bool
    {
        $payload = [
            'channel' => $this->channelId,
            'text' => $message,
        ];

        $jsonPayload = json_encode($payload);

        $ch = curl_init('https://slack.com/api/chat.postMessage');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->botToken,
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($jsonPayload)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);
        return $httpCode === 200 && ($data['ok'] ?? false);
    }

    public function formatAbsencesMessage(array $processedAbsences, string $startDate, string $endDate): string
    {
        $message = [];

        // Add header
        $startDateObj = new \DateTime($startDate);
        $endDateObj = new \DateTime($endDate);
        $message[] = "*Absences from " . $startDateObj->format('d.m.Y') . " to " . $endDateObj->format('d.m.Y') . "*";
        $message[] = "";

        if (empty($processedAbsences)) {
            $message[] = "No absences found for this period.";
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

            // Format absences grouped by person
            foreach ($absencesByPerson as $personName => $absences) {
                $message[] = "*" . $personName . "*";

                foreach ($absences as $absence) {
                    $absenceStartDate = new \DateTime($absence['start']);
                    $absenceEndDate = new \DateTime($absence['end']);
                    $displayStartDate = $absenceStartDate->format('d.m.Y');
                    $displayEndDate = (clone $absenceEndDate)->modify('-1 day')->format('d.m.Y');

                    $absenceText = $displayStartDate . " to " . $displayEndDate;
                    if (isset($absence['daysCount']) && $absence['daysCount'] > 0) {
                        $absenceText .= " (" . $absence['daysCount'] . " days)";
                    }

                    $message[] = "• " . $absenceText;
                }
                $message[] = "";
            }
        }

        // Add summary
        $message[] = "_Total absences: " . count($processedAbsences) . "_";

        return implode("\n", $message);
    }
}
