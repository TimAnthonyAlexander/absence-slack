<?php

namespace App\Config;

use Dotenv\Dotenv;

class Config
{
    private array $config = [];
    private array $allowedNames = [];

    public function __construct(string $rootDir)
    {
        // Load environment variables
        $dotenv = Dotenv::createImmutable($rootDir);
        $dotenv->load();

        // Set config values from environment
        $this->config = [
            'apiId' => $_ENV['API_ID'],
            'apiKey' => $_ENV['API_KEY'],
            'filterReasonId' => $_ENV['FILTER_REASON_ID'] ?? '60daf6bab5dc1f0a17142ab4',
            'botToken' => $_ENV['SLACK_BOT_TOKEN'] ?? null,
            'slackChannelId' => $_ENV['SLACK_CHANNEL_ID'] ?? 'C095S8BQHJ5',
            'enableSlackNotifications' => isset($_ENV['ENABLE_SLACK_NOTIFICATIONS']) ?
                filter_var($_ENV['ENABLE_SLACK_NOTIFICATIONS'], FILTER_VALIDATE_BOOL) : false,
        ];

        // Load allowed names
        $this->loadAllowedNames($rootDir);
    }

    private function loadAllowedNames(string $rootDir): void
    {
        $allowedNamesPath = $rootDir . '/allowed_names.php';
        if (file_exists($allowedNamesPath)) {
            $this->allowedNames = require $allowedNamesPath;
        }
    }

    public function get(string $key)
    {
        return $this->config[$key] ?? null;
    }

    public function getAllowedNames(): array
    {
        return $this->allowedNames;
    }

    public function isSlackEnabled(): bool
    {
        return $this->config['enableSlackNotifications'];
    }
}
