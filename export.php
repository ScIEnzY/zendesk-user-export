<?php

// Load environment variables
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// CONFIG
$zendeskDomain = $_ENV['ZENDESK_DOMAIN'];
$zendeskEmail = $_ENV['ZENDESK_EMAIL'];
$apiToken = $_ENV['ZENDESK_API_TOKEN'];

//  AUTH: email/token + base64
$authString = base64_encode("$zendeskEmail/token:$apiToken");

// Crea la cartella debug_json se non esiste
if (!is_dir('debug_json')) {
    mkdir('debug_json');
}

$allUsers = [];
$nextPage = "https://$zendeskDomain/api/v2/users.json";

while ($nextPage) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $nextPage);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Basic $authString"
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Debug: Save raw JSON response in debug_json/
    file_put_contents("debug_json/zendesk_response_page_" . count($allUsers) . ".json", $response);
    echo "Raw API response saved to debug_json/zendesk_response_page_" . count($allUsers) . ".json\n";

    // Debug: Print curl info if there's an error
    if ($httpCode !== 200) {
        echo "Curl Error: " . curl_error($ch) . "\n";
        echo "Curl Info: " . print_r(curl_getinfo($ch), true) . "\n";
        die("Errore API Zendesk: codice $httpCode");
    }

    curl_close($ch);

    $data = json_decode($response, true);
    $users = $data["users"] ?? [];
    $allUsers = array_merge($allUsers, $users);
    
    echo "Fetched " . count($users) . " users in this page. Total users so far: " . count($allUsers) . "\n";
    
    // Get next page URL if it exists
    $nextPage = $data["next_page"] ?? null;
}

echo "Total users fetched: " . count($allUsers) . "\n";

// Create XML
$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Phonebook></Phonebook>');

foreach ($allUsers as $user) {
    
    if (empty($user["email"]) && empty($user["phone"])) {
        continue;
    }

    $fullName = $user["name"];
    $email = $user["email"] ?? '';
    $phone = $user["phone"] ?? '';

    // Split name and last name
    $parts = explode(" ", $fullName, 2);
    $firstName = $parts[0];
    $lastName = $parts[1] ?? '';

    $contact = $xml->addChild("Contact");
    $contact->addChild("FirstName", htmlspecialchars($firstName));
    $contact->addChild("LastName", htmlspecialchars($lastName));
    $contact->addChild("MobileNumber", htmlspecialchars($phone));
    $contact->addChild("EmailAddress", htmlspecialchars($email));
}

// Save XML
file_put_contents("3cx_phonebook.xml", $xml->asXML());

echo "Phonebook exported to 3cx_phonebook.xml with " . count($xml->Contact) . " contacts.\n";

// Export to CSV for Google Workspace Contacts
$csvFile = fopen("google_contacts.csv", "w");
// Google Contacts CSV header
fputcsv($csvFile, [
    'First Name', 'Last Name', 'Email Address', 'Mobile'
]);
foreach ($allUsers as $user) {
    if (empty($user["email"]) && empty($user["phone"])) {
        continue;
    }
    $fullName = $user["name"];
    $email = $user["email"] ?? '';
    $phone = $user["phone"] ?? '';
    $parts = explode(" ", $fullName, 2);
    $firstName = $parts[0];
    $lastName = $parts[1] ?? '';
    fputcsv($csvFile, [
        $firstName,
        $lastName,
        $email,
        $phone
    ]);
}
fclose($csvFile);
echo "Contacts also exported to google_contacts.csv for Google Workspace.\n";

