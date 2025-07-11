# Absence Fetcher

A modular PHP application for fetching and displaying absence data from Absence.io API.

## Setup

1. Clone the repository
2. Run `composer install` to install dependencies
3. Copy `.env.example` to `.env` and fill in your API credentials
4. Copy `allowed_names.php.example` to `allowed_names.php` and add the names you want to track

## Usage

```bash
php scripts/fetch_absences.php [start_date] [end_date]
```

Where:
- `start_date`: Optional start date in YYYY-MM-DD format (defaults to today)
- `end_date`: Optional end date in YYYY-MM-DD format (defaults to today)

Example:
```bash
php scripts/fetch_absences.php 2025-07-10 2025-07-15
```

## Project Structure

- `scripts/fetch_absences.php` - Main entry point script
- `src/` - Application source code
  - `Config/` - Configuration handling
  - `Api/` - API client for Absence.io
  - `Cli/` - Command line interface handling
  - `Data/` - Data processing and filtering
  - `Command/` - Main command classes 