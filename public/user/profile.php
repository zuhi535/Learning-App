<?php
session_start();

// Ellenőrizzük, hogy a felhasználó be van-e jelentkezve
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Adatbázis kapcsolat
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "voltizap";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}

// Felhasználó adatainak lekérése
$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM users WHERE id='$user_id'");

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // Ha nincs barátkód, generálunk egyet
    if (empty($user['friend_code'])) {
        require_once __DIR__ . '/../includes/functions.php';
        $friend_code = generateFriendCode($conn);
        $conn->query("UPDATE users SET friend_code = '$friend_code' WHERE id = '$user_id'");
        $user['friend_code'] = $friend_code;
    }
} else {
    echo "Felhasználó nem található!";
    exit();
}

// Üzenetek kezelése
$error = isset($_SESSION['error']) ? $_SESSION['error'] : null;
$success = isset($_SESSION['success']) ? $_SESSION['success'] : null;
unset($_SESSION['error']);
unset($_SESSION['success']);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fff;
            margin-top: 2rem;
        }
        .profile-container h1 {
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto;
            display: block;
        }
        .profile-info {
            margin-top: 1.5rem;
        }
        .profile-info p {
            margin-bottom: 0.5rem;
        }
        .btn-container {
            margin-top: 1.5rem;
            text-align: center;
        }
        .btn-container a {
            margin: 0 0.5rem;
        }
        .add-friend-container, .friends-list {
            margin-top: 2rem;
            padding: 1rem;
            border: 1px solid #eee;
            border-radius: 5px;
        }
        .error-message {
            color: red;
            margin-bottom: 1rem;
        }
        .success-message {
            color: green;
            margin-bottom: 1rem;
        }
        .friend-code {
            font-family: monospace;
            background-color: #f8f9fa;
            padding: 2px 6px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }

        /* Sötét mód stílusok */
        body.dark-mode {
            background-color: #121212 !important;
            color: white !important;
        }
        .dark-mode .profile-container {
            background-color: #1e1e1e;
            color: white;
            border-color: #333;
        }
        .dark-mode .add-friend-container,
        .dark-mode .friends-list {
            border-color: #333;
            background-color: #2a2a2a;
        }
        .dark-mode .btn-primary {
            background-color: #bb86fc;
            border-color: #bb86fc;
        }
        .dark-mode .btn-warning {
            background-color: #ffa726;
            border-color: #ffa726;
        }
        .dark-mode .friend-code {
            background-color: #2a2a2a;
            border-color: #444;
            color: #fff;
        }
    </style>
</head>
<body class="bg-light" id="pageBody">
    <div class="profile-container">
        <h1>Profil</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <!-- Profilkép megjelenítése -->
        <img src="uploads/<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profilkép" class="profile-picture">

        <!-- Felhasználó adatainak megjelenítése -->
        <div class="profile-info">
            <p><strong>Felhasználónév:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>E-mail:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Barátkód:</strong> 
                <span class="friend-code"><?php echo htmlspecialchars($user['friend_code']); ?></span>
                <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyFriendCode()">Másolás</button>
            </p>
            <p><strong>Neme:</strong> 
                <?php
                if ($user['gender'] == 'male') {
                    echo 'Férfi';
                } elseif ($user['gender'] == 'female') {
                    echo 'Nő';
                } else {
                    echo 'Egyéb';
                }
                ?>
            </p>
            <p><strong>Pontszám:</strong> <?php echo htmlspecialchars($user['score']); ?></p>
        </div>

        <!-- Gombok -->
        <div class="btn-container">
            <a href="../dashboard.php" class="btn btn-primary">Vissza a dashboardra</a>
            <a href="edit_profile.php" class="btn btn-warning">Profil szerkesztése</a>
        </div>

        <!-- Barát hozzáadása form -->
        <div class="add-friend-container mt-4">
            <h3>Barát hozzáadása</h3>
            <form action="/Learning-App-main/public/user/friends/send_request.php" method="post">
                <div class="input-group mb-3">
                    <input type="text" name="friend_code" class="form-control" placeholder="Barátkód" required>
                    <button class="btn btn-success" type="submit">Hozzáad</button>
                </div>
            </form>
        </div>

        <!-- Barátok listája -->
        <div class="friends-list mt-4">
            <h3>Barátaid</h3>
            <?php
            // Frissített lekérdezés, ami ellenőrzi, hogy létezik-e a status oszlop
            $check_status = $conn->query("SHOW COLUMNS FROM user_friends LIKE 'status'");
            if ($check_status->num_rows > 0) {
                // Ha van status oszlop
                $friends_query = "SELECT u.id, u.username, u.profile_pic 
                                FROM users u 
                                JOIN user_friends uf ON u.id = uf.friend_id 
                                WHERE uf.user_id = '$user_id' AND uf.status = 'accepted'";
            } else {
                // Ha nincs status oszlop
                $friends_query = "SELECT u.id, u.username, u.profile_pic 
                                FROM users u 
                                JOIN user_friends uf ON u.id = uf.friend_id 
                                WHERE uf.user_id = '$user_id'";
            }
            
            $friends_result = $conn->query($friends_query);
            
            if ($friends_result->num_rows > 0) {
                echo '<ul class="list-group">';
                while ($friend = $friends_result->fetch_assoc()) {
                    echo '<li class="list-group-item d-flex align-items-center">';
                    echo '<img src="uploads/' . htmlspecialchars($friend['profile_pic']) . '" alt="Profilkép" class="rounded-circle me-3" width="40" height="40">';
                    echo htmlspecialchars($friend['username']);
                    echo '</li>';
                }
                echo '</ul>';
            } else {
                echo '<p>Még nincsenek barátaid.</p>';
            }
            ?>
        </div>

        <!-- Barátkérelmek linkje -->
        <div class="friend-requests mt-4">
        <a href="friends/friend_requests.php" class="btn btn-info">Barátkérelmek megtekintése</a>

        </div>

        <!-- Sötét mód váltó gomb -->
        <div class="btn-container mt-3">
            <button id="toggleDarkMode" class="btn btn-secondary">Sötét mód</button>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Sötét mód JavaScript -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const body = document.getElementById("pageBody");
            const toggleDarkModeBtn = document.getElementById("toggleDarkMode");
            
            if (localStorage.getItem("darkMode") === "enabled") {
                body.classList.add("dark-mode");
            }
            
            toggleDarkModeBtn.addEventListener("click", function () {
                body.classList.toggle("dark-mode");
                
                if (body.classList.contains("dark-mode")) {
                    localStorage.setItem("darkMode", "enabled");
                } else {
                    localStorage.removeItem("darkMode");
                }
            });
        });

        // Barátkód másolása funkció
        function copyFriendCode() {
            const friendCode = document.querySelector('.friend-code').textContent;
            navigator.clipboard.writeText(friendCode)
                .then(() => {
                    alert('Barátkód másolva!');
                })
                .catch(err => {
                    console.error('Hiba a másolás során:', err);
                    // Alternatív másolási módszer
                    const textarea = document.createElement('textarea');
                    textarea.value = friendCode;
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);
                    alert('Barátkód másolva!');
                });
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>