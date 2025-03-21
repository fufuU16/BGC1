<?php
include 'db_connection.php';

// Get the time range from the request, default to '7days'
$timeRange = isset($_GET['range']) ? $_GET['range'] : '7days';

// Determine the interval based on the time range
switch ($timeRange) {
    case '3months':
        $interval = 'INTERVAL 3 MONTH';
        break;
    case '6months':
        $interval = 'INTERVAL 6 MONTH';
        break;
    case 'year':
        $interval = 'INTERVAL 1 YEAR';
        break;
    case '7days':
    default:
        $interval = 'INTERVAL 7 DAY';
        break;
}

// Update the query to filter based on the time range and calculate overall passengers
$query = "
    SELECT date, 
           route, 
           SUM(passengers) as total_passengers
    FROM passenger_data 
    WHERE date >= CURDATE() - $interval
    GROUP BY date, route
    ORDER BY date ASC
";

$result = $conn->query($query);
$passengerData = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $passengerData[] = $row;
    }
}

// Calculate overall passengers for each date
$overallData = [];
foreach ($passengerData as $data) {
    $date = $data['date'];
    if (!isset($overallData[$date])) {
        $overallData[$date] = 0;
    }
    $overallData[$date] += $data['total_passengers'];
}

// Add overall passengers to each entry
foreach ($passengerData as &$data) {
    $data['overall_passengers'] = $overallData[$data['date']];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($passengerData);
exit;
?>