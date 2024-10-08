<?php 

include '../../debug_config.php'; 
include '../db_connect.php';

if (!isset($_GET['table_name'])) {
    echo json_encode(["status" => "error", "message" => "Error: table_name is a mandatory parameter"]);
    exit;
}

$table_name = $_GET['table_name'];

if (!preg_match('/^[a-zA-Z0-9_]+$/', $table_name)) {
    echo json_encode(["status" => "error", "message" => "Invalid table name.: " . $db_conn->error]);
    exit;
}

// Check if 'purpose' parameter is provided
$purpose = isset($_GET['purpose']) ? $_GET['purpose'] : null;

$sql = "SELECT * FROM `$table_name`";

// If 'purpose' is provided, append it to the query
if ($purpose !== null) {
    $sql .= " WHERE `purpose` = ?";
}

$stmt = $db_conn->prepare($sql);

if ($purpose !== null) {
    // Bind the 'purpose' parameter if it exists
    $stmt->bind_param('s', $purpose);
}

$stmt->execute();
$result = $stmt->get_result();

$response = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $response[] = $row;
    }
    $result->free();
    echo json_encode(["status" => "success", "data" => $response]);
} else {
    echo json_encode(["status" => "error", "message" => "Error fetching entries: " . $db_conn->error]);
}

$stmt->close();
$db_conn->close();
?>
