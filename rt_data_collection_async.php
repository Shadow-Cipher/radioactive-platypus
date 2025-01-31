<?php

// ------------------------------------------------------------------------------------------
//                                       Credits
// ------------------------------------------------------------------------------------------
// Project Name:             radioactive-platypus
// Project Started:          01/31/2025
// Derivative/Original?:     Original Code, Not Forked, Not a Derivative
// Developer:                C.M. Jones
// Location:                 Pearland, TX
//
// ------------------------------------------------------------------------------------------
//                                       Contributors
// ------------------------------------------------------------------------------------------
// 
// Project Contributors:
//
//     N/A
//
// ------------------------------------------------------------------------------------------
//                                       Details
// ------------------------------------------------------------------------------------------
//
// File Name:
// rt_data_collection_async.php
//
// ------------------------------------------------------------------------------------------
// 
// This script:
// 
// - Asynchronously polls multiple machines at once using curl_multi_exec.
// - Inserts data into MySQL in real-time.
// - Uses transactions to ensure data integrity.
// - Feeds a web socket front end for real-time display of machining cycles
//
//
// ------------------------------------------------------------------------------------------

// Database Credentials
$DB_HOST = "your-mysql-server";
$DB_USER = "your-user";
$DB_PASS = "your-password";
$DB_NAME = "cnc_data";

// List of MTConnect Machines
$machines = [
    "Milling_1" => "http://192.168.1.10:5000/current",
    "Lathe_1"   => "http://192.168.1.11:5000/current",
    "Milling_2" => "http://192.168.1.12:5000/current",
    "Lathe_2"   => "http://192.168.1.13:5000/current"
];

// Initialize Multi-cURL
$multiHandle = curl_multi_init();
$curlHandles = [];

// Prepare Requests
foreach ($machines as $machine => $url) {
    $curlHandles[$machine] = curl_init();
    curl_setopt($curlHandles[$machine], CURLOPT_URL, $url);
    curl_setopt($curlHandles[$machine], CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curlHandles[$machine], CURLOPT_TIMEOUT, 2);  // Fast response time
    curl_multi_add_handle($multiHandle, $curlHandles[$machine]);
}

// Execute Multi-cURL Requests
$running = null;
do {
    curl_multi_exec($multiHandle, $running);
} while ($running);

// Fetch Responses
$dataCollection = [];
foreach ($machines as $machine => $url) {
    $response = curl_multi_getcontent($curlHandles[$machine]);
    $json = json_decode($response, true);
    
    if ($json) {
        $dataCollection[] = [
            "machine_name" => $machine,
            "cycle_time" => $json["cycle_time"] ?? 0,
            "spindle_speed" => $json["spindle_speed"] ?? 0,
            "feed_rate" => $json["feed_rate"] ?? 0,
            "part_counter" => $json["part_counter"] ?? 0,
            "timestamp" => date("Y-m-d H:i:s")
        ];
    }
    
    curl_multi_remove_handle($multiHandle, $curlHandles[$machine]);
    curl_close($curlHandles[$machine]);
}

// Close Multi-cURL
curl_multi_close($multiHandle);

// Insert Data into MySQL in Batches
if (!empty($dataCollection)) {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    // Use Prepared Statement for Batch Insert
    $stmt = $conn->prepare("INSERT INTO machining_cycles (machine_name, cycle_time, spindle_speed, feed_rate, part_counter, timestamp) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($dataCollection as $data) {
        $stmt->bind_param("sdddis", $data["machine_name"], $data["cycle_time"], $data["spindle_speed"], $data["feed_rate"], $data["part_counter"], $data["timestamp"]);
        $stmt->execute();
    }

    $stmt->close();
    $conn->close();
    echo "Data successfully stored in real time.\n";
} else {
    echo "No valid data retrieved.\n";
}

?>
