<?php
include '../../debug_config.php'; 
include '../db_connect.php';

function checkForUpdates($db_conn, $tableName, $lastUpdate, $purpose = null) {
    $query = "SELECT UNIX_TIMESTAMP(MAX(updated_at)) AS last_updated FROM `$tableName`";
    
    if (!is_null($purpose)) {
        $query .= " WHERE `purpose` = ?";
    }

    $stmt = $db_conn->prepare($query);
    
    if (!$stmt) {
        return array("status" => "error", "message" => "Query preparation failed: " . $db_conn->error);
    }

    if (!is_null($purpose)) {
        $stmt->bind_param('s', $purpose);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $currentUpdateTime = $row['last_updated'] ? intval($row['last_updated']) : 0;
    $hasUpdates = $currentUpdateTime > $lastUpdate;

    return array("status" => "success", 'hasUpdates' => $hasUpdates, 'last_update' => $currentUpdateTime);
}

$lastUpdate = isset($_REQUEST['last_update']) ? intval($_REQUEST['last_update']) : 0;
$tableName = isset($_REQUEST['table_name']) ? $_REQUEST['table_name'] : null;
$purpose = isset($_REQUEST['purpose']) ? $_REQUEST['purpose'] : null;

if (is_null($tableName) || empty($tableName)) {
    echo json_encode(array("status" => "error", "message" => "Table name is required."));
    exit;
}

$updateInfo = checkForUpdates($db_conn, $tableName, $lastUpdate, $purpose);

header('Content-Type: application/json');
if ($updateInfo['status'] === "error") {
    echo json_encode($updateInfo); 
} else {
    echo json_encode(array(
        "status" => "success",
        "hasUpdates" => $updateInfo['hasUpdates'],
        "last_update" => $updateInfo['last_update']
    ));
}

$db_conn->close(); 
?>
