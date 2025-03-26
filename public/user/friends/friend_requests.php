<?php
session_start();
require_once __DIR__ . '/../../../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Barátkérelmek lekérése
$stmt = $conn->prepare("SELECT u.id, u.username, u.profile_pic, uf.id as request_id 
                       FROM user_friends uf 
                       JOIN users u ON uf.user_id = u.id 
                       WHERE uf.friend_id = ? AND uf.status = 'pending'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$requests_result = $stmt->get_result();

// Barátkérelmek kezelése
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];
    
    if ($action == 'accept') {
        // Elfogadjuk a kérelmet
        $stmt = $conn->prepare("UPDATE user_friends SET status = 'accepted' WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        
        // Hozzáadjuk a fordított kapcsolatot is
        $stmt = $conn->prepare("SELECT user_id, friend_id FROM user_friends WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $request_data = $stmt->get_result()->fetch_assoc();
        
        $stmt = $conn->prepare("INSERT INTO user_friends (user_id, friend_id, status) 
                              VALUES (?, ?, 'accepted')");
        $stmt->bind_param("ii", $request_data['friend_id'], $request_data['user_id']);
        $stmt->execute();
        
        $_SESSION['success'] = "Barátkérelem elfogadva!";
    } elseif ($action == 'reject') {
        $stmt = $conn->prepare("DELETE FROM user_friends WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $_SESSION['success'] = "Barátkérelem elutasítva!";
    }
    
    header("Location: /user/friends/friend_requests.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Barátkérelmek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <h1>Barátkérelmek</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if ($requests_result->num_rows > 0): ?>
            <ul class="list-group">
                <?php while ($request = $requests_result->fetch_assoc()): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <img src="/uploads/<?= htmlspecialchars($request['profile_pic']) ?>" class="rounded-circle me-3" width="40" height="40">
                        <?= htmlspecialchars($request['username']) ?>
                        <div>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="request_id" value="<?= $request['request_id'] ?>">
                                <input type="hidden" name="action" value="accept">
                                <button type="submit" class="btn btn-success btn-sm">Elfogad</button>
                            </form>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="request_id" value="<?= $request['request_id'] ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="btn btn-danger btn-sm">Elutasít</button>
                            </form>
                        </div>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <div class="alert alert-info">Nincsenek függőben lévő barátkérelmeid.</div>
        <?php endif; ?>
        
        <a href="/user/profile.php" class="btn btn-primary mt-3">Vissza a profilhoz</a>
    </div>
</body>
</html>
