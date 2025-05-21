# Zendesk User Export Tool

A simple PHP script to export Zendesk users to a 3CX-compatible phonebook XML format and Google Contact CSV file.


## Features

- Exports all Zendesk users
- Supports both email and phone contacts
- Generates 3CX-compatible XML
- Generates Google compatible CSV
- Saves raw API responses for debugging
- Handles pagination automatically

## Requirements

- PHP 7.0 or higher
- cURL extension
- Zendesk API access
- Composer

## Installation

1. Clone the repository:
   ```bash
   git clone [repository-url]
   cd [repository-name]
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Configure environment:
   ```bash
   cp .env-example .env
   ```
   Then edit `.env` with your Zendesk credentials.

## Configuration

Edit the following variables in `.env`:

```env
ZENDESK_DOMAIN=your-domain.zendesk.com
ZENDESK_EMAIL=your-email@example.com
ZENDESK_API_TOKEN=your-api-token
```

## Usage

1. Configure your Zendesk credentials in `.env`
2. Run the script:
   ```bash
   php export.php
   ```
3. The script will generate:
   - `3cx_phonebook.xml`: The final phonebook file
   - `google_contacts.csv`: CSV file compatible with Google Workspace Contacts
   - `debug_json/`: Directory containing raw API responses

## Output Files

- `3cx_phonebook.xml`: The main phonebook file in 3CX format
- `google_contacts.csv`: CSV file compatible with Google Workspace Contacts
- `debug_json/zendesk_response_page_*.json`: Raw API responses for debugging

## License

This project is open source and available under the MIT License.

## Author

Federico Colbertaldo - ScIEnzY 