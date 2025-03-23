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

    $sql = "SELECT * FROM users WHERE email='$email'"; // Nem szűrünk role alapján
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error_message = "Hibás jelszó!";
        }
    } else {
        $error_message = "Nincs ilyen felhasználó!";
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bejelentkezés</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .login-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 2rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fff;
            margin-top: 5rem;
        }
        .login-container h2 {
            margin-bottom: 1.5rem;
        }
        .error-message {
            color: red;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="login-container">
        <h2 class="text-center">Bejelentkezés</h2>
        <?php if (isset($error_message)): ?>
            <div class="error-message text-center"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control" placeholder="E-mail" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Jelszó</label>
                <input type="password" name="password" class="form-control" placeholder="Jelszó" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Bejelentkezés</button>
        </form>
        <div class="text-center mt-3">
            <p>Nincs még fiókod? <a href="register.php" class="btn btn-link">Regisztrálj itt!</a></p>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>