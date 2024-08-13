<?php

require 'vendor/autoload.php'; // Load Composer's autoloader

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

function get7RoomsReservation() {

    $svnrm_resrv_payload = file_get_contents("7rooms_reserv-payload.json");

    $svnrm_resrv_payload_json = json_decode($svnrm_resrv_payload, TRUE);

    // Extract reservation details
    $reservation = $svnrm_resrv_payload_json[0]['entity'];

    $reservationId = $svnrm_resrv_payload_json[0]['entity']['id'];
    $firstName = $svnrm_resrv_payload_json[0]['entity']['first_name'];
    $lastName = $svnrm_resrv_payload_json[0]['entity']['last_name'];
    $email = $svnrm_resrv_payload_json[0]['entity']['email'];
    $status = $svnrm_resrv_payload_json[0]['entity']['status'];
    $city = $svnrm_resrv_payload_json[0]['entity']['city'];
    $state = $svnrm_resrv_payload_json[0]['entity']['state'];
    $postalCode = $svnrm_resrv_payload_json[0]['entity']['postal_code'];
    $notes = $svnrm_resrv_payload_json[0]['entity']['notes'];
    $confirmationCode = $svnrm_resrv_payload_json[0]['entity']['client_reference_code'];
    $totalPrice = $svnrm_resrv_payload_json[0]['entity']['total_payment'];
    $prepayment = $svnrm_resrv_payload_json[0]['entity']['prepayment'];

    // Extract venue ID
    $venueId = $reservation['venue_id'];

    // Call get-venue-details.php with the venue ID
    $venueName = getVenueDetails($venueId);

    // Reservation Details
    $reservation_type = get7roomsReservationDetails($reservationId, 'reservation_type');
    $prepayment_tax = get7roomsReservationDetails($reservationId, 'prepayment_tax');
    $prepayment_total = get7roomsReservationDetails($reservationId, 'prepayment_total');
    $prepayment_net = get7roomsReservationDetails($reservationId, 'prepayment_net');
    $prepayment_gratuity = get7roomsReservationDetails($reservationId, 'prepayment_gratuity');
    $prepayment_service_charge = get7roomsReservationDetails($reservationId, 'prepayment_service_charge');
    $onsite_payment_tax = get7roomsReservationDetails($reservationId, 'onsite_payment_tax');
    $onsite_payment_net = get7roomsReservationDetails($reservationId, 'onsite_payment_net');
    $onsite_payment_total = get7roomsReservationDetails($reservationId, 'onsite_payment_total');
    $onsite_payment_gratuity = get7roomsReservationDetails($reservationId, 'onsite_payment_gratuity');
    $total_net_payment = get7roomsReservationDetails($reservationId, 'total_net_payment');
    $total_gross_payment = get7roomsReservationDetails($reservationId, 'total_gross_payment');

    echo "<h2>Sample 7 Rooms Reservation Data</h2>";
    // Display venue name
    echo "<br /><strong> Reservation Details </strong><br />";    
    echo "Venue Name: ";
    echo $venueName . "<br />";
    echo "Confirmation Code: $confirmationCode<br />";
    echo "Reservation Type: $reservation_type<br />";
    echo "<br /><strong> Payment Details </strong><br />" ; 
   // echo "Total Payment: $totalPrice<br />";    
    echo "Prepayment Net: $prepayment_net<br />";    
    echo "Prepayment Tax: $prepayment_tax<br />";    
    echo "Prepayment Service Charge: $prepayment_service_charge<br />";    
    echo "Prepayment Gratuity: $prepayment_gratuity<br />";    
    echo "Prepayment Total: $prepayment_total<br />";    
    echo "Onsite Payment Net: $onsite_payment_net<br />";    
    echo "Onsite Payment Tax: $onsite_payment_tax<br />";    
    echo "Onsite Payment Gratuity: $onsite_payment_gratuity<br />";    
    echo "Total Net Payment: $total_net_payment<br />";    
    echo "Total Gross Payment: $total_gross_payment<br />";    
    // Customer Details
    echo "<br /><strong> Customer Details </strong><br />";
    echo "First Name: $firstName<br />";
    echo "Last Name: $lastName<br />";
    echo "Email: $email<br />";
    echo "Status: $status<br />";
    echo "City: $city<br />";
    echo "State: $state<br />";
    echo "Postal Code: $postalCode<br />";
    echo "Notes: $notes<br />";

    echo "<br /><br />DEV NOTE: Only submit data into QBO when status is 'Complete'. If we recieve a modified reservation with status 'cancelled', we search QBO transactions with matching refId and mark cancelled/refunded if found.";
}

function getVenueDetails($venueId) {

    // Define SevenRooms API credentials
    $sevenRoomsClientId = '7a729494e3471c5d326614848c91ef595f087fcdf3ac361eb6cfbf81c30f5ee36c57676d590607e40dc92bcc6c47e487bfdef8628ae56a0c4724e12966f7b3ad';
    $sevenRoomsClientSecret = '76569eda151567a713de45455033175aee294d06b20c8f49c2dd1f823704e9624f3377f1c5f3e564bffbcc43e6a17f2815584b9b8068d615e8129b26b35e911d';

    // Initialize GuzzleHttp client
    $client = new Client([
        'base_uri' => 'https://demo.sevenrooms.com/api-ext/2_4/', // SevenRooms API base URL
        'verify' => false, // Disable SSL verification (for testing only)
    ]);

    try {
        // Authenticate with SevenRooms API
        $response = $client->request('POST', 'auth', [
            'form_params' => [
                'client_id' => $sevenRoomsClientId,
                'client_secret' => $sevenRoomsClientSecret,
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        $accessToken = $data['data']['token'];

        // Fetch venue details from SevenRooms
        $response = $client->request('GET', 'venues/' . $venueId, [
            'headers' => [
                'Authorization' => $accessToken,
            ],
        ]);

        $venueDetails = json_decode($response->getBody(), true);

        // Output venue name
        return $venueDetails['data']['name'];
    } catch (RequestException $e) {
        // Handle GuzzleHttp exceptions
        if ($e->hasResponse()) {
            $responseBody = $e->getResponse()->getBody();
            echo "Error: " . $responseBody . "<br />";
        } else {
            echo "Error: " . $e->getMessage() . "<br />";
        }
    } 
}

function get7roomsReservationDetails($reservationId, $fieldName) {

    // Define SevenRooms API credentials
    $sevenRoomsClientId = '7a729494e3471c5d326614848c91ef595f087fcdf3ac361eb6cfbf81c30f5ee36c57676d590607e40dc92bcc6c47e487bfdef8628ae56a0c4724e12966f7b3ad';
    $sevenRoomsClientSecret = '76569eda151567a713de45455033175aee294d06b20c8f49c2dd1f823704e9624f3377f1c5f3e564bffbcc43e6a17f2815584b9b8068d615e8129b26b35e911d';

    // Initialize GuzzleHttp client
    $client = new Client([
        'base_uri' => 'https://demo.sevenrooms.com/api-ext/2_4/', // SevenRooms API base URL
        'verify' => false, // Disable SSL verification (for testing only)
    ]);

    try {
        // Authenticate with SevenRooms API
        $response = $client->request('POST', 'auth', [
            'form_params' => [
                'client_id' => $sevenRoomsClientId,
                'client_secret' => $sevenRoomsClientSecret,
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        $accessToken = $data['data']['token'];

        // Fetch venue details from SevenRooms
        $response = $client->request('GET', 'reservations/' . $reservationId, [
            'headers' => [
                'Authorization' => $accessToken,
            ],
        ]);

        $reservationDetails = json_decode($response->getBody(), true);

        // Output venue name
        return $reservationDetails['data'][$fieldName];
    } catch (RequestException $e) {
        // Handle GuzzleHttp exceptions
        if ($e->hasResponse()) {
            $responseBody = $e->getResponse()->getBody();
            echo "Reservations Error: " . $responseBody . "<br />";
        } else {
            echo "Reservations Error: " . $e->getMessage() . "<br />";
        }
    } 
}

function getHostawayReservation() {
    $ha_resrv_payload = file_get_contents("hostaway_reserv-payload.json");
    $ha_resrv_payload_json = json_decode($ha_resrv_payload, TRUE);

    $firstName = $ha_resrv_payload_json['guestFirstName'];
    $lastName = $ha_resrv_payload_json['guestLastName'];
    $email = $ha_resrv_payload_json['guestEmail'];
    $status = $ha_resrv_payload_json['status'];
    $confirmationCode = $ha_resrv_payload_json['confirmationCode'];
    $channelName = $ha_resrv_payload_json['channelName'];
    $totalPrice = $ha_resrv_payload_json['totalPrice'];
    $city = $ha_resrv_payload_json['guestCity'];
    //$state = $ha_resrv_payload_json[0]['entity']['state'];
    $postalCode = $ha_resrv_payload_json['guestZipCode'];
    $notes = $ha_resrv_payload_json['guestNote'];

    echo "<h2>Sample Hostaway Reservation Data</h2>";
    // Display venue name
    echo "Channel Name: ";
    echo $channelName . "<br />";
    // Output the extracted data
    echo "Confirmation Code: $confirmationCode<br />";
    echo "Total Price (USD): $totalPrice<br />";
    echo "First Name: $firstName<br />";
    echo "Last Name: $lastName<br />";
    echo "Email: $email<br />";
    echo "Status: $status<br />";
    echo "City: $city<br />";
    echo "State & Zip Code: $postalCode<br />";
    echo "Notes: $notes<br />";

    echo "<br /><br />DEV NOTE: Only submit data into QBO when status is 'new'. If we recieve a modified reservation with status 'cancelled', we search QBO transactions with matching refId and mark cancelled/refunded if found.";
}

get7RoomsReservation();
getHostawayReservation()
?>
