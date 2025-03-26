<?php
// Az adatbázis kapcsolódás
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "voltizap";

// Kapcsolódás az adatbázishoz
$conn = new mysqli($servername, $username, $password, $dbname);

// Ellenőrizzük a kapcsolatot
if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}

// Barátkód generáló függvény
function generateFriendCode() {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';
    for ($i = 0; $i < 8; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

// Ellenőrizzük, hogy az űrlap adatokat küldtek-e
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Az űrlapról kapott adatok
    $username = $_POST['username'];
    $email = $_POST['email'];
    $birthdate = $_POST['birthdate'];
    $gender = $_POST['gender'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $gdpr_consent = isset($_POST['gdpr_consent']) ? 1 : 0;
    $role = $_POST['role']; // Jogosultság kiválasztása

    // Jelszó ellenőrzése
    if ($password !== $password_confirm) {
        $error_message = "A jelszavak nem egyeznek!";
    } else {
        // Jelszó hash-elése bcrypt-el
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Egyedi barátkód generálása
        $friend_code = generateFriendCode();

        // Ellenőrizzük, hogy az email cím nem létezik-e már
        $check_email_sql = "SELECT * FROM users WHERE email = '$email'";
        $result = $conn->query($check_email_sql);

        if ($result->num_rows > 0) {
            $error_message = "Ez az email cím már foglalt.";
        } else {
            // SQL lekérdezés a felhasználó hozzáadására
            $sql = "INSERT INTO users (username, email, birthdate, gender, password, gdpr_consent, role, friend_code) 
                    VALUES ('$username', '$email', '$birthdate', '$gender', '$hashed_password', '$gdpr_consent', '$role', '$friend_code')";

            if ($conn->query($sql) === TRUE) {
                $success_message = "Regisztráció sikeres!";
            } else {
                $error_message = "Hiba a regisztráció során: " . $conn->error;
            }
        }
    }
}

// Kapcsolat lezárása
$conn->close();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Felhasználói regisztráció</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .registration-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fff;
            margin-top: 2rem;
        }
        .registration-container h1 {
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .error-message {
            color: red;
            margin-bottom: 1rem;
            text-align: center;
        }
        .success-message {
            color: green;
            margin-bottom: 1rem;
            text-align: center;
        }
    </style>
</head>
<body class="bg-light">
    <div class="registration-container">
        <h1>Felhasználói regisztráció</h1>
        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if (isset($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <form action="register.php" method="post">
            <div class="mb-3">
                <label for="username" class="form-label">Felhasználónév</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">E-mail cím</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="birthdate" class="form-label">Születési idő</label>
                <input type="date" id="birthdate" name="birthdate" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="gender" class="form-label">Neme</label>
                <select id="gender" name="gender" class="form-control" required>
                    <option value="male">Férfi</option>
                    <option value="female">Nő</option>
                    <option value="other">Egyéb</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Jelszó</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password_confirm" class="form-label">Jelszó újra</label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Jogosultság</label>
                <select id="role" name="role" class="form-control" required>
                    <option value="user">Felhasználó</option>
                    <option value="admin">Adminisztrátor</option>
                </select>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" id="gdpr_consent" name="gdpr_consent" class="form-check-input" required>
                <label for="gdpr_consent" class="form-check-label">GDPR nyilatkozatot elolvastam, elfogadom</label>
            </div>
            <button type="submit" class="btn btn-primary w-100">Regisztrálok</button>
        </form>
        <div class="text-center mt-3">
            <p>Már van fiókod? <a href="login.php" class="btn btn-link">Jelentkezz be itt!</a></p>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>