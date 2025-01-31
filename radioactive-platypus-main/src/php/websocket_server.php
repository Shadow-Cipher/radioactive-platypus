<?php

// ------------------------------------------------------------------------------------------
//                                       Credits
// ------------------------------------------------------------------------------------------
// 
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
// websocket_server.php
// 
// ------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------
// 
// PHP WebSocket Server
// powers "dashboard.html" 
//
//
// Requires:
//
// "php composer" and
// "Ratchet" (PHP WebSocket library)
//
// make sure you have composer 
// installed and then install 
// ratchet by doing the following:
//
// bash: (to install ratchet)
//
// ____
// #$:>   composer require cboden/ratchet
//
// ------------------------------------------------------------------------------------------


require 'vendor/autoload.php';
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

// WebSocket Server Class
class CNCWebSocketServer implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        // Handle incoming messages if needed
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }

    public function sendData() {
        $machines = [
            "Milling_1" => "http://192.168.1.10:5000/current",
            "Lathe_1"   => "http://192.168.1.11:5000/current"
        ];

        while (true) {
            $machineData = [];

            foreach ($machines as $machine => $url) {
                $json = file_get_contents($url);
                $data = json_decode($json, true);

                if ($data) {
                    $machineData[$machine] = [
                        "cycle_time" => $data["cycle_time"] ?? 0,
                        "spindle_speed" => $data["spindle_speed"] ?? 0,
                        "feed_rate" => $data["feed_rate"] ?? 0,
                        "part_counter" => $data["part_counter"] ?? 0,
                        "timestamp" => date("Y-m-d H:i:s")
                    ];
                }
            }

            // Broadcast to all WebSocket clients
            foreach ($this->clients as $client) {
                $client->send(json_encode($machineData));
            }

            sleep(1); // Adjust polling interval as needed
        }
    }
}

// Start WebSocket Server
$server = new Ratchet\App('0.0.0.0', 8080);
$cncServer = new CNCWebSocketServer();
$server->route('/cnc', $cncServer, ['*']);
echo "WebSocket Server Started on ws://localhost:8080/cnc\n";

// Run Data Collection in Background
pcntl_fork() === 0 ? $cncServer->sendData() : $server->run();
?>
