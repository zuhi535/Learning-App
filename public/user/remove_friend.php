<?php
session_start();
include '../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $friendId = $_POST['friendId'];
    $userId = $_SESSION['user_id'];

    // Barát törlése a barátlistáról
    $stmt = $conn->prepare("DELETE FROM user_friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)");
    $stmt->bind_param("iiii", $userId, $friendId, $friendId, $userId);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Barát sikeresen törölve!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Hiba történt a barát törlése során!']);
    }
}
?>