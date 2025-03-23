<?php
session_start();
include '../../config/config.php';

// Adatbázis kapcsolat UTF-8 kódolásának beállítása
$conn->set_charset("utf8");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $friendCode = $_POST['friendCode'];
    $senderId = $_SESSION['user_id'];

    // Barátkód alapján a célfelhasználó ID lekérése
    $stmt = $conn->prepare("SELECT id FROM users WHERE friend_code = ?");
    $stmt->bind_param("s", $friendCode);
    $stmt->execute();
    $result = $stmt->get_result();
    $receiver = $result->fetch_assoc();

    if ($receiver) {
        $receiverId = $receiver['id'];

        // Ellenőrizzük, hogy a felhasználó ne küldjön felkérést saját magának
        if ($senderId == $receiverId) {
            echo json_encode(['status' => 'error', 'message' => 'Nem küldhetsz felkérést saját magadnak!'], JSON_UNESCAPED_UNICODE);
            exit();
        }

        // Ellenőrizzük, hogy a célfelhasználó már szerepel-e a barátlistán
        $stmt = $conn->prepare("
            SELECT * FROM user_friends 
            WHERE (user_id = ? AND friend_id = ?) 
            OR (user_id = ? AND friend_id = ?)
        ");
        $stmt->bind_param("iiii", $senderId, $receiverId, $receiverId, $senderId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'A felhasználó már szerepel a barátlistádban!'], JSON_UNESCAPED_UNICODE);
            exit();
        }

        // Ellenőrizzük, hogy már létezik-e függőben lévő barátfelkérés
        $stmt = $conn->prepare("
            SELECT * FROM friend_requests 
            WHERE ((sender_id = ? AND receiver_id = ?) 
            OR (sender_id = ? AND receiver_id = ?))
            AND status = 'pending'
        ");
        $stmt->bind_param("iiii", $senderId, $receiverId, $receiverId, $senderId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Már létezik függőben lévő barátfelkérés!'], JSON_UNESCAPED_UNICODE);
            exit();
        }

        // Új barátfelkérés beszúrása
        $stmt = $conn->prepare("INSERT INTO friend_requests (sender_id, receiver_id, status) VALUES (?, ?, 'pending')");
        $stmt->bind_param("ii", $senderId, $receiverId);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Barátfelkérés sikeresen elküldve!'], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Hiba történt a felkérés küldése során!'], JSON_UNESCAPED_UNICODE);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Érvénytelen barátkód!'], JSON_UNESCAPED_UNICODE);
    }
}
?>