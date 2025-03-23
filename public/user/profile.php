<?php
session_start();

// Ellenőrizzük, hogy a felhasználó be van-e jelentkezve
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Adatbázis kapcsolat
include '../../config/config.php'; // Frissítsd az elérési utat, ha szükséges

// Felhasználó adatainak lekérése
$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM users WHERE id='$user_id'");

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "Felhasználó nem található!";
    exit();
}

// Barátok listájának lekérése
$friends_result = $conn->query("
    SELECT u.id, u.username, u.profile_pic, u.email, u.gender 
    FROM user_friends uf 
    JOIN users u ON uf.friend_id = u.id 
    WHERE uf.user_id='$user_id'
");
$friends = $friends_result->fetch_all(MYSQLI_ASSOC);

// Függőben lévő barátfelkérések lekérése
$pending_requests_result = $conn->query("
    SELECT fr.id, u.username 
    FROM friend_requests fr 
    JOIN users u ON fr.sender_id = u.id 
    WHERE fr.receiver_id='$user_id' AND fr.status='pending'
");
$pending_requests = $pending_requests_result->fetch_all(MYSQLI_ASSOC);
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
            max-width: 600px;
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
        .dark-mode .btn-primary {
            background-color: #bb86fc;
            border-color: #bb86fc;
        }
        .dark-mode .btn-warning {
            background-color: #ffa726;
            border-color: #ffa726;
        }

        /* Felugró üzenet stílusok */
        .alert-popup {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 15px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }

        .alert-popup.show {
            opacity: 1;
        }

        .alert-popup.success {
            background-color: green;
        }

        .alert-popup.error {
            background-color: red;
        }
    </style>
</head>
<body class="bg-light" id="pageBody">
    <div class="profile-container">
        <h1>Profil</h1>
        
        <!-- Profilkép megjelenítése -->
        <img src="uploads/<?php echo $user['profile_pic']; ?>" alt="Profilkép" class="profile-picture">

        <!-- Felhasználó adatainak megjelenítése -->
        <div class="profile-info">
            <p><strong>Felhasználónév:</strong> <?php echo $user['username']; ?></p>
            <p><strong>E-mail:</strong> <?php echo $user['email']; ?></p>
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
            <!-- Pontszám megjelenítése -->
            <p><strong>Pontszám:</strong> <?php echo $user['score']; ?></p>
            <!-- Streak megjelenítése -->
            <p><strong>Streak:</strong> <?php echo $user['streak']; ?> nap</p>
            <!-- Barátkód megjelenítése -->
            <p><strong>Barátkód:</strong> <?php echo $user['friend_code']; ?></p>
        </div>

        <!-- Barátok hozzáadása -->
        <div class="profile-info">
            <h2>Barátok</h2>
            <form action="add_friend.php" method="POST">
                <div class="mb-3">
                    <label for="friendCode" class="form-label">Barátkód</label>
                    <input type="text" class="form-control" id="friendCode" name="friendCode" required>
                </div>
                <button type="submit" class="btn btn-primary">Barát hozzáadása</button>
            </form>
        </div>

        <!-- Függőben lévő barátfelkérések megjelenítése -->
        <div class="profile-info">
            <h3>Függőben lévő barátfelkérések</h3>
            <ul>
                <?php foreach ($pending_requests as $request): ?> 
                <li>
                    <?php echo $request['username']; ?>
                    <form action="accept_friend_request.php" method="POST" style="display:inline;">
                        <input type="hidden" name="requestId" value="<?php echo $request['id']; ?>">
                        <button type="submit" name="action" value="accept" class="btn btn-success btn-sm">Elfogad</button>
                        <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Elutasít</button>
                    </form>
                </li>
            <?php endforeach; ?>

            </ul>
        </div>

        <!-- Barátok listájának megjelenítése -->
        <div class="profile-info">
            <h3>Barátaid</h3>
            <ul>
                <?php foreach ($friends as $friend): ?>
                    <li>
                        <?php echo $friend['username']; ?>
                        <button class="btn btn-info btn-sm" onclick="viewFriendProfile(<?php echo $friend['id']; ?>)">Profil megtekintése</button>
                        <form action="remove_friend.php" method="POST" style="display:inline;">
                            <input type="hidden" name="friendId" value="<?php echo $friend['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Törlés</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Gombok -->
        <div class="btn-container">
            <a href="../dashboard.php" class="btn btn-primary">Vissza a dashboardra</a>
            <a href="edit_profile.php" class="btn btn-warning">Profil szerkesztése</a>
        </div>

        <!-- Sötét mód váltó gomb -->
        <div class="btn-container">
            <button id="toggleDarkMode" class="btn btn-secondary">Sötét mód</button>
        </div>
    </div>

    <!-- Barát profiljának megtekintése modal -->
    <div class="modal fade" id="friendProfileModal" tabindex="-1" aria-labelledby="friendProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="friendProfileModalLabel">Barát profilja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="friendProfileContent">
                    <!-- A barát profiljának tartalma ide kerül -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bezárás</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Sötét mód és felugró üzenetek JavaScript -->
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

        // Felugró üzenetek megjelenítése
        function showAlert(message, type) {
            const alertPopup = document.createElement('div');
            alertPopup.className = `alert-popup ${type}`;
            alertPopup.textContent = message;

            document.body.appendChild(alertPopup);

            // Megjelenítés
            setTimeout(() => {
                alertPopup.classList.add('show');
            }, 100);

            // Eltűntetés 3 másodperc után
            setTimeout(() => {
                alertPopup.classList.remove('show');
                setTimeout(() => {
                    document.body.removeChild(alertPopup);
                }, 500); // Várakozás az animáció befejezésére
            }, 3000);
        }

        // Barát hozzáadása form kezelése
        document.querySelector('form[action="add_friend.php"]').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('add_friend.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showAlert(data.message, 'success');
                } else {
                    showAlert(data.message, 'error');
                }
                // Frissítjük az oldalt, hogy a változások megjelenjenek
                setTimeout(() => {
                    window.location.reload();
                }, 3000);
            })
            .catch(error => {
                showAlert('Hiba történt a kérés feldolgozása során!', 'error');
            });
        });

        // Barátfelkérések elfogadása vagy elutasítása
        document.querySelectorAll('form[action="accept_friend_request.php"]').forEach(form => {
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const requestId = formData.get('requestId');
        const action = formData.get('action');

        fetch('accept_friend_request.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ requestId, action })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showAlert(data.message, 'success');
            } else {
                showAlert(data.message, 'error');
            }
            setTimeout(() => { window.location.reload(); }, 2000);
        })
        .catch(() => {
            showAlert('Hiba történt a kérés feldolgozása során!', 'error');
        });
    });
});



        // Barátok törlése
        document.querySelectorAll('form[action="remove_friend.php"]').forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(this);

                fetch('remove_friend.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showAlert(data.message, 'success');
                    } else {
                        showAlert(data.message, 'error');
                    }
                    // Frissítjük az oldalt, hogy a változások megjelenjenek
                    setTimeout(() => {
                        window.location.reload();
                    }, 3000);
                })
                .catch(error => {
                    showAlert('Hiba történt a kérés feldolgozása során!', 'error');
                });
            });
        });

        // Barát profiljának megtekintése
        function viewFriendProfile(friendId) {
            fetch(`get_friend_profile.php?friendId=${friendId}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('friendProfileContent').innerHTML = data;
                    const modal = new bootstrap.Modal(document.getElementById('friendProfileModal'));
                    modal.show();
                });
        }
    </script>
</body>
</html>