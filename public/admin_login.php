<?php
session_start();
include '../config/config.php'; // Helyes elérési út

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Ellenőrizzük, hogy az adatbáziskapcsolat létezik-e
    if (!isset($conn)) {
        die("Adatbáziskapcsolat nem jött létre!");
    }

    $sql = "SELECT * FROM users WHERE email='$email' AND role='admin'"; // Csak adminok
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            header("Location: admin_dashboard.php");
            exit();
        } else {
            echo "Hibás jelszó!";
        }
    } else {
        echo "Nincs ilyen admin felhasználó!";
    }
}
?>
<form method="post">
    <input type="email" name="email" required placeholder="Admin E-mail">
    <input type="password" name="password" required placeholder="Admin Jelszó">
    <button type="submit">Admin Bejelentkezés</button>
</form>