<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;

$accessToken = 'eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..OtpiVYACSzr7heGBaERPWw.vcXoIoZ4Fb4UrKFvVOtWUnPeHvnlR5o886nEKihqhL7Dn2rRPzg6eSS7iYnpCrXpwvYM8CdLzYiNVto3DoJfwZFswFOnlYt4bqbuWl4VBUcmjNz2mrkG3_JDL4Vs7Jbz-ywI7l3zJzxwxsOaoim65jDXZ_RG1nsZ8nGRkkIkp-SxBGP2fMrsZkbeNiaw6aODZ_F-Ba3OO74Eu5H8T9v7XuJ7f0uID-EXVDrVtXJcZ5J1aoyH04KrlbIW7dQcSWFVBqtHShXDLkIDYqmoUgovz1nN9SZZ8_JTVO_Bzgz_PbnhxwAb0fDOdEUEgF-tzFOwjylt2l_7WNdDj2bTFbu7PP8FO5rInaW8ChSHsDGxlsfvHpqe1nwsvZ4Tb--ZcPx76Sz9x5WqRV0PcBpLe9_154ZyuD8KuEk4l4J72QTXkIuHpQlD5_XpRGTM7BwldyYh5zGRycESxMhaVreXqahb93-VDqjKEBSrhrXv1E9EI53DHUFlkbQycLuWInqn7QQvX4bblfSUU9Z3IHARXUushdeGNfmdDRFoNw8DeL6tH12wGw3JqUdRT8-_x3REuPxf4slUXZJifOdGrPPGPX6S02-s4lnhIr6BVYbV4FXJdKc5Ec7b0qDeCIwCEgkKMITqLTrnMqQXYq5TURrNNpmHMBerPYy8n2yKUShwAE2AWscJzExbbRcdTm6NUHV5fWXfZgBH6Qw6MGb_tlzSh2ttrvvNXO8vLRvBANDOjU7tO04.ThNG7J-73rxH8LOLGLdfnw';
$realmId = '9341452470090095';
$baseUri = 'https://sandbox-quickbooks.api.intuit.com/v3/company/';

$channelName = 'airbnb';
$confirmationCode = 'HMP2KQ4B49';
$totalPrice = 267;
$firstName = 'Mark';
$lastName = 'Anderson';
$email = 'mail@test.com';
$status = 'new';
$city = 'Austin';
$stateZip = 'TX 78705';
$notes = '';
$transactionDate;
$paymentType;

$itemName = "{$channelName} Booking";
$customerFullName = "{$firstName} {$lastName}";

// Create a new Guzzle client
$client = new Client([
    'base_uri' => $baseUri,
    'verify' => false, // Disable SSL verification (for testing only)
]);

function getItemId($client, $accessToken, $realmId, $itemName) {
    try {
        $response = $client->get("{$realmId}/query", [
            'headers' => [
                'Authorization' => "Bearer {$accessToken}",
                'Accept' => 'application/json'
            ],
            'query' => [
                'query' => "SELECT * FROM Item WHERE Name = '$itemName'"
            ]
        ]);

        $responseData = json_decode($response->getBody()->getContents(), true);
        if (!empty($responseData['QueryResponse']['Item'])) {
            return $responseData['QueryResponse']['Item'][0]['Id'];
        }
    } catch (\GuzzleHttp\Exception\RequestException $e) {
        echo "Error fetching item: " . $e->getMessage() . "\n";
        if ($e->hasResponse()) {
            echo "Response: " . $e->getResponse()->getBody()->getContents() . "\n";
        }
    }
    return null;
}

function createItem($client, $accessToken, $realmId, $itemName) {
    $itemData = [
        "Name" => $itemName,
        "IncomeAccountRef" => [
            "value" => "1" // Replace with your Income Account ID (must be numeric)
        ],
        "Type" => "Service"
    ];

    try {
        $response = $client->post("{$realmId}/item", [
            'headers' => [
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'body' => json_encode($itemData)
        ]);

        $responseData = json_decode($response->getBody()->getContents(), true);
        return $responseData['Item']['Id'];

    } catch (\GuzzleHttp\Exception\RequestException $e) {
        echo "Error creating item: " . $e->getMessage() . "\n";
        if ($e->hasResponse()) {
            echo "Response: " . $e->getResponse()->getBody()->getContents() . "\n";
        }
    }
    return null;
}

function getCustomerId($client, $accessToken, $realmId, $customerFullName) {
    try {
        $response = $client->get("{$realmId}/query", [
            'headers' => [
                'Authorization' => "Bearer {$accessToken}",
                'Accept' => 'application/json'
            ],
            'query' => [
                'query' => "SELECT * FROM Customer WHERE DisplayName = '$customerFullName'"
            ]
        ]);

        $responseData = json_decode($response->getBody()->getContents(), true);
        if (!empty($responseData['QueryResponse']['Customer'])) {
            return $responseData['QueryResponse']['Customer'][0]['Id'];
        }
    } catch (\GuzzleHttp\Exception\RequestException $e) {
        echo "Error fetching customer: " . $e->getMessage() . "\n";
        if ($e->hasResponse()) {
            echo "Response: " . $e->getResponse()->getBody()->getContents() . "\n";
        }
    }
    return null;
}

function createCustomer($client, $accessToken, $realmId, $firstName, $lastName, $email, $city, $stateZip) {
    $customerData = [
        "GivenName" => $firstName,
        "FamilyName" => $lastName,
        "DisplayName" => "{$firstName} {$lastName}",
        "PrimaryEmailAddr" => [
            "Address" => $email
        ],
        "BillAddr" => [
            "City" => $city,
            "CountrySubDivisionCode" => "TX",
            "PostalCode" => $stateZip
        ]
    ];

    try {
        $response = $client->post("{$realmId}/customer", [
            'headers' => [
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'body' => json_encode($customerData)
        ]);

        $responseData = json_decode($response->getBody()->getContents(), true);
        return $responseData['Customer']['Id'];

    } catch (\GuzzleHttp\Exception\RequestException $e) {
        echo "Error creating customer: " . $e->getMessage() . "\n";
        if ($e->hasResponse()) {
            echo "Response: " . $e->getResponse()->getBody()->getContents() . "\n";
        }
    }
    return null;
}

function checkDuplicateTransaction($client, $accessToken, $realmId, $confirmationCode) {
    $query = "SELECT * FROM SalesReceipt WHERE PaymentRefNum = '$confirmationCode'";

    try {
        $response = $client->get("{$realmId}/query", [
            'headers' => [
                'Authorization' => "Bearer {$accessToken}",
                'Accept' => 'application/json'
            ],
            'query' => [
                'query' => $query,
                'minorversion' => 62  // Specify the minor version if necessary
            ]
        ]);

        $data = json_decode($response->getBody(), true);
        $salesReceipts = $data['QueryResponse']['SalesReceipt'] ?? [];

        return !empty($salesReceipts);

    } catch (RequestException $e) {
        if ($e->hasResponse()) {
            echo "Error: " . $e->getResponse()->getBody();
        } else {
            echo "Request failed: " . $e->getMessage();
        }
    }
    return false;
}

// Check if item exists, otherwise create it
$itemId = getItemId($client, $accessToken, $realmId, $itemName);
if (!$itemId) {
    $itemId = createItem($client, $accessToken, $realmId, $itemName);
    if (!$itemId) {
        die("Failed to create or retrieve the item.");
    }
}

// Check if customer exists, otherwise create it
$customerId = getCustomerId($client, $accessToken, $realmId, $customerFullName);
if (!$customerId) {
    $customerId = createCustomer($client, $accessToken, $realmId, $firstName, $lastName, $email, $city, $stateZip);
    if (!$customerId) {
        die("Failed to create or retrieve the customer.");
    }
}

// Check for duplicate transaction
if (checkDuplicateTransaction($client, $accessToken, $realmId, $confirmationCode)) {
    die("Duplicate transaction found. No new transaction created.");
}

// Prepare the sales receipt data
$salesReceiptData = [
    "Line" => [
        [
            "Amount" => $totalPrice,
            "DetailType" => "SalesItemLineDetail",
            "SalesItemLineDetail" => [
                "ItemRef" => [
                    "value" => $itemId,
                    "name" => $itemName
                ]
            ]
        ]
    ],
    "PaymentRefNum" => $confirmationCode,
    "CustomerRef" => [
        "value" => $customerId
    ],
    "BillEmail" => [
        "Address" => $email
    ],
    "BillAddr" => [
        "City" => $city,
        "CountrySubDivisionCode" => "TX",
        "PostalCode" => "78705"
    ],
    "ShipAddr" => [
        "City" => $city,
        "CountrySubDivisionCode" => "TX",
        "PostalCode" => "78705"
    ],
    "TotalAmt" => $totalPrice,
    "PrivateNote" => $notes,
    "CustomField" => [
        [
            "DefinitionId" => "1", // Replace with your Custom Field ID
            "Name" => "Confirmation Code",
            "Type" => "StringType",
            "StringValue" => $confirmationCode
        ]
    ]
];

try {
    $response = $client->post("{$realmId}/salesreceipt", [
        'headers' => [
            'Authorization' => "Bearer {$accessToken}",
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ],
        'body' => json_encode($salesReceiptData)
    ]);

    $responseData = json_decode($response->getBody()->getContents(), true);
    echo "Transaction created successfully:\n";
    print_r($responseData);

} catch (\GuzzleHttp\Exception\RequestException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    if ($e->hasResponse()) {
        echo "Response: " . $e->getResponse()->getBody()->getContents() . "\n";
    }
}
?>
