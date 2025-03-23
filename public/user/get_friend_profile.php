<?php
session_start();
include '../../config/config.php';

if (isset($_GET['friendId'])) {
    $friendId = $_GET['friendId'];
    $result = $conn->query("SELECT * FROM users WHERE id='$friendId'");

    if ($result->num_rows > 0) {
        $friend = $result->fetch_assoc();
        echo "
            <p><strong>Felhasználónév:</strong> {$friend['username']}</p>
            <p><strong>E-mail:</strong> {$friend['email']}</p>
            <p><strong>Neme:</strong> 
                " . ($friend['gender'] == 'male' ? 'Férfi' : ($friend['gender'] == 'female' ? 'Nő' : 'Egyéb')) . "
            </p>
            <p><strong>Profilkép:</strong></p>
            <img src='uploads/{$friend['profile_pic']}' alt='Profilkép' class='profile-picture'>
        ";
    } else {
        echo "Barát nem található!";
    }
}
?>