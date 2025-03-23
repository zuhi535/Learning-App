<?php
session_start();
include '../../config/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Nincs bejelentkezve!"]);
    exit();
}

$user_id = $_SESSION['user_id'];

// Adatok fogadása POST formából vagy JSON-ből
$request_id = isset($_POST['requestId']) ? intval($_POST['requestId']) : null;
$action = isset($_POST['action']) ? $_POST['action'] : null;

// Ha JSON-t kapunk, akkor abból is kinyerjük az adatokat
if ($request_id === null || $action === null) {
    $data = json_decode(file_get_contents("php://input"), true);
    $request_id = isset($data['requestId']) ? intval($data['requestId']) : null;
    $action = $data['action'] ?? null;
}

if (!$request_id || !$action) {
    echo json_encode(["status" => "error", "message" => "Hiányzó adatok!"]);
    exit();
}

// Lekérdezzük a felkérést
$query = $conn->prepare("SELECT sender_id FROM friend_requests WHERE id = ? AND receiver_id = ? AND status = 'pending'");
$query->bind_param("ii", $request_id, $user_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Barátfelkérés nem található vagy már kezelve lett."]);
    exit();
}

$row = $result->fetch_assoc();
$sender_id = $row['sender_id'];

if ($action === "accept") {
    // Hozzáadás a barátlistához
    $conn->query("INSERT INTO user_friends (user_id, friend_id) VALUES ($user_id, $sender_id), ($sender_id, $user_id)");
    $conn->query("UPDATE friend_requests SET status='accepted' WHERE id = $request_id");
    echo json_encode(["status" => "success", "message" => "Barátfelkérés elfogadva!"]);
} elseif ($action === "reject") {
    $conn->query("DELETE FROM friend_requests WHERE id = $request_id");
    echo json_encode(["status" => "success", "message" => "Barátfelkérés elutasítva!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Érvénytelen művelet!"]);
}
?>
