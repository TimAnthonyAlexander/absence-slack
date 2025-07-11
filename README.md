# Absence Fetcher

A modular PHP application for fetching and displaying absence data from the Absence.io API.

## Setup

1. Clone the repository
2. Run `composer install` to install dependencies
3. Create a `.env` file in the project root with the following variables:

   ```env
   API_ID=your_absenceio_api_id
   API_KEY=your_absenceio_api_key
   TEAM_ID=your_team_id
   # Optional:
   FILTER_REASON_ID=your_filter_reason_id
   SLACK_BOT_TOKEN=your_slack_bot_token
   SLACK_CHANNEL_ID=your_slack_channel_id
   ENABLE_SLACK_NOTIFICATIONS=true
   ```

4. Copy `allowed_names.php.example` to `allowed_names.php` and add the names you want to track (one per line, as a PHP array).

## Usage

### Fetch absences for a custom date range

```bash
php scripts/fetch_absences.php [start_date] [end_date]
```

- `start_date`: Optional, format YYYY-MM-DD (defaults to today)
- `end_date`: Optional, format YYYY-MM-DD (defaults to today)

Example:
```bash
php scripts/fetch_absences.php 2025-07-10 2025-07-15
```

### Fetch absences for next week (Monday–Sunday)

```bash
php scripts/fetch_next_week.php
```

## Project Structure

- `scripts/` – Entry point scripts
  - `fetch_absences.php` – Fetch absences for a given date range
  - `fetch_next_week.php` – Fetch absences for the upcoming week
- `src/` – Application source code
  - `Config/` – Configuration handling
  - `Api/` – API client for Absence.io
  - `Cli/` – Command line argument parsing
  - `Data/` – Data processing and filtering
  - `Notification/` – Slack notification integration
  - `Command/` – Main command classes

## Slack Integration

To enable Slack notifications, set `ENABLE_SLACK_NOTIFICATIONS=true` and provide `SLACK_BOT_TOKEN` and `SLACK_CHANNEL_ID` in your `.env` file. If not set, Slack notifications will be disabled by default. 