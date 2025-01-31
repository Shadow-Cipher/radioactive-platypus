<?php

// Changed from HTML file to PHP file.

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
// dashboard.php
// 
// ------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------
// 
// This script:
// 
// - Uses a web socket to render machining cycles from networked machines in real-time.
// - The backend code uses asyncronous PHP and JavaScript to collect the machine metrics in
//   real-time from multiple CNCs., and at the same time, continuously updates records in the 
//   MySQL metrics data-base using SQL transactions.
// - The SQL database is for using the data in various ways in the future, the web socket
//   updates the display for real-time metrics prior to storing the data in the database
//
//
// ------------------------------------------------------------------------------------------

?>



<!--Frontend WebSocket Dashboard-->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CNC Live Dashboard</title>
    <script>
        let ws = new WebSocket("ws://localhost:8080/cnc");

        ws.onmessage = function(event) {
            let data = JSON.parse(event.data);
            let output = "<h2>Machine Live Data</h2><table border='1'><tr><th>Machine</th><th>Cycle Time</th><th>Spindle Speed</th><th>Feed Rate</th><th>Part Counter</th></tr>";
            
            for (let machine in data) {
                output += `<tr>
                    <td>${machine}</td>
                    <td>${data[machine].cycle_time}</td>
                    <td>${data[machine].spindle_speed}</td>
                    <td>${data[machine].feed_rate}</td>
                    <td>${data[machine].part_counter}</td>
                </tr>`;
            }
            
            output += "</table>";
            document.getElementById("machineData").innerHTML = output;
        };
    </script>
</head>
<body>
    <h1>CNC Real-Time Dashboard</h1>
    <div id="machineData">Waiting for data...</div>
</body>
</html>
