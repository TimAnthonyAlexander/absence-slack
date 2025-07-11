# Team Absence Reporter

Fetch, display, and share your team’s absences from Absence.io—instantly, beautifully, and optionally right in Slack.

---

## What It Does

- **Connects to Absence.io**: Securely fetches your team’s absence data.
- **Clear, Minimal Output**: Presents absences in a clean, readable format for the terminal.
- **Slack Integration**: With a single command, send absence summaries to any Slack channel via your bot.

---

## Quick Start

1. **Install dependencies**
   ```bash
   composer install
   ```
2. **Configure your environment**
   - Create a `.env` file in the project root:
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
   - Copy `allowed_names.php.example` to `allowed_names.php` and list the team members you want to track.

---

## Usage

### Show Absences for Any Date Range
```bash
php scripts/fetch_absences.php [start_date] [end_date]
```
- Dates in `YYYY-MM-DD` format. Defaults to today if omitted.
- Example:
  ```bash
  php scripts/fetch_absences.php 2025-07-10 2025-07-15
  ```

### Show Absences for Next Week
```bash
php scripts/fetch_next_week.php
```

---

## Slack Integration

Send absence summaries directly to your team’s Slack channel:
- Set `ENABLE_SLACK_NOTIFICATIONS=true` in your `.env`.
- Provide your `SLACK_BOT_TOKEN` and `SLACK_CHANNEL_ID`.
- When enabled, every fetch will also post a summary to Slack—no extra steps.

---

## Structure

- `scripts/` — Entry points
  - `fetch_absences.php` — Custom date range
  - `fetch_next_week.php` — Next week’s absences
- `src/` — Modular PHP source
  - `Api/` — Absence.io API client
  - `Cli/` — Command-line argument parsing
  - `Config/` — Environment and config
  - `Data/` — Absence processing
  - `Notification/` — Slack integration
  - `Command/` — Main orchestration

---

## Minimal, Confident, Effortless

No clutter. No noise. Just your team’s absences—where you need them, when you need them.