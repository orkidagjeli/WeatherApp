<?php
include_once("config.php");

header('Content-Type: application/json');

// Fetch weather data from the API
$url = "https://melchior.moja.it:8085/weather-api/get_weather?lat=41.3281007&lon=139.6917";
$response = file_get_contents($url);

if ($response === FALSE) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["error" => "Unable to fetch data from the API."]);
    exit();
}

$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    echo json_encode(["error" => "Invalid JSON received from the API."]);
    exit();
}

if (!isset($data['current_temp'], $data['feels_like'], $data['weather_description'])) {
    http_response_code(500);
    echo json_encode(["error" => "Missing expected data from the API response."]);
    exit();
}

$current_temp = $data['current_temp'];
$feels_like = $data['feels_like'];
$weather_description = $data['weather_description'];

// Begin a transaction to ensure data integrity
$conn->begin_transaction();

try {
    
    // Insert the new data into the database
    $insertStmt = $conn->prepare("INSERT INTO weather_data (current_temp, feels_like, weather_description) VALUES (?, ?, ?)");
    if ($insertStmt === FALSE) {
        throw new Exception("Failed to prepare INSERT statement: " . $conn->error);
    }

    $insertStmt->bind_param("dds", $current_temp, $feels_like, $weather_description);

    if (!$insertStmt->execute()) {
        throw new Exception("Failed to insert data: " . $insertStmt->error);
    }
    $insertStmt->close();

    // Commit the transaction
    $conn->commit();

    // Fetch weather data from the database
    $sql = "SELECT * FROM weather_data ORDER BY timestamp DESC";
    $result = $conn->query($sql);

    if ($result === FALSE) {
        throw new Exception("Failed to fetch data from the database: " . $conn->error);
    }

    $rows = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode($rows);

} catch (Exception $e) {
    // Rollback the transaction in case of an error
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}

// Close the connection
$conn->close();
?>
