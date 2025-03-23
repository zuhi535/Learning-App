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

// Adatok frissítése
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $field = $_POST['field']; // Melyik mezőt szerkesztjük
    $value = $_POST['value']; // Az új érték

    // Ha sablon profilkép van kiválasztva
    if ($field == 'profile_pic' && isset($_POST['selected_profile_pic'])) {
        $value = $_POST['selected_profile_pic'];
    }

    // Jelszó hash-elése, ha a jelszó mezőt szerkesztjük
    if ($field == 'password') {
        $value = password_hash($value, PASSWORD_DEFAULT);
    }

    // Adatok frissítése az adatbázisban
    $sql = "UPDATE users SET $field='$value' WHERE id='$user_id'";

    if ($conn->query($sql) === TRUE) {
        echo "Profil adatok sikeresen frissítve!";
        header("Location: profile.php");
        exit();
    } else {
        echo "Hiba a frissítés során: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil szerkesztése</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .edit-profile-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fff;
            margin-top: 2rem;
        }
        .edit-profile-container h1 {
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
        .edit-form {
            display: none; /* Alapértelmezés szerint elrejtjük a szerkesztő űrlapot */
        }
        .action-buttons {
            margin-top: 2rem;
            text-align: center;
        }
        .default-profile-pics {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 1rem;
        }
        .default-profile-pics img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
            border: 2px solid transparent;
        }
        .default-profile-pics img.selected {
            border-color: #007bff;
        }

        /* Sötét mód stílusok */
        body.dark-mode {
            background-color: #121212 !important;
            color: white !important;
        }
        .dark-mode .edit-profile-container {
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
        .dark-mode .btn-secondary {
            background-color: #3700b3;
            border-color: #3700b3;
        }
        .dark-mode .default-profile-pics img {
            border-color: #555;
        }
        .dark-mode .default-profile-pics img.selected {
            border-color: #bb86fc;
        }
    </style>
</head>
<body class="bg-light" id="pageBody">
    <div class="edit-profile-container">
        <h1>Profil szerkesztése</h1>
        
        <!-- Profilkép megjelenítése -->
        <img src="uploads/<?php echo $user['profile_pic']; ?>" alt="Profilkép" class="profile-picture">

        <!-- Felhasználó adatainak megjelenítése -->
        <div class="profile-info">
            <!-- Felhasználónév -->
            <p>
                <strong>Felhasználónév:</strong> 
                <span id="username-value"><?php echo $user['username']; ?></span>
                <button onclick="editField('username')" class="btn btn-sm btn-warning">Edit</button>
                <form id="username-form" class="edit-form" method="post" onsubmit="return saveField('username')">
                    <input type="text" name="value" value="<?php echo $user['username']; ?>" required>
                    <input type="hidden" name="field" value="username">
                    <button type="submit" class="btn btn-sm btn-success">Mentés</button>
                    <button type="button" onclick="cancelEdit('username')" class="btn btn-sm btn-secondary">Mégse</button>
                </form>
            </p>

            <!-- E-mail -->
            <p>
                <strong>E-mail:</strong> 
                <span id="email-value"><?php echo $user['email']; ?></span>
                <button onclick="editField('email')" class="btn btn-sm btn-warning">Edit</button>
                <form id="email-form" class="edit-form" method="post" onsubmit="return saveField('email')">
                    <input type="email" name="value" value="<?php echo $user['email']; ?>" required>
                    <input type="hidden" name="field" value="email">
                    <button type="submit" class="btn btn-sm btn-success">Mentés</button>
                    <button type="button" onclick="cancelEdit('email')" class="btn btn-sm btn-secondary">Mégse</button>
                </form>
            </p>

            <!-- Jelszó -->
            <p>
                <strong>Jelszó:</strong> 
                <span id="password-value">********</span>
                <button onclick="editField('password')" class="btn btn-sm btn-warning">Edit</button>
                <form id="password-form" class="edit-form" method="post" onsubmit="return saveField('password')">
                    <input type="password" name="value" placeholder="Új jelszó" required>
                    <input type="hidden" name="field" value="password">
                    <button type="submit" class="btn btn-sm btn-success">Mentés</button>
                    <button type="button" onclick="cancelEdit('password')" class="btn btn-sm btn-secondary">Mégse</button>
                </form>
            </p>

            <!-- Profilkép -->
            <p>
                <strong>Profilkép:</strong> 
                <span id="profile_pic-value"><?php echo $user['profile_pic']; ?></span>
                <button onclick="editField('profile_pic')" class="btn btn-sm btn-warning">Edit</button>
                <form id="profile_pic-form" class="edit-form" method="post" onsubmit="return saveField('profile_pic')">
                    <div class="default-profile-pics">
                        <img src="uploads/default1.jpg" alt="Default 1" onclick="selectProfilePic('default1.jpg')">
                        <img src="uploads/default2.jpg" alt="Default 2" onclick="selectProfilePic('default2.jpg')">
                        <img src="uploads/default3.jpg" alt="Default 3" onclick="selectProfilePic('default3.jpg')">
                        <img src="uploads/default4.jpg" alt="Default 4" onclick="selectProfilePic('default4.jpg')">
                    </div>
                    <input type="hidden" name="field" value="profile_pic">
                    <input type="hidden" id="selected-profile-pic" name="selected_profile_pic">
                    <button type="submit" class="btn btn-sm btn-success">Mentés</button>
                    <button type="button" onclick="cancelEdit('profile_pic')" class="btn btn-sm btn-secondary">Mégse</button>
                </form>
            </p>
        </div>

        <!-- Mentés és Mégsem gombok -->
        <div class="action-buttons">
            <button type="button" onclick="submitAllForms()" class="btn btn-primary" disabled>Mentés</button>
            <button type="button" onclick="window.location.href='profile.php'" class="btn btn-secondary">Mégsem</button>
        </div>

        <!-- Sötét mód váltó gomb -->
        <div class="text-center mt-4">
            <button id="toggleDarkMode" class="btn btn-secondary">Sötét mód</button>
        </div>
    </div>

    <!-- JavaScript a szerkesztéshez -->
    <script>
        function editField(field) {
            // Elrejtjük az értéket és az Edit gombot
            document.getElementById(field + '-value').style.display = 'none';
            document.querySelector(`button[onclick="editField('${field}')"]`).style.display = 'none';

            // Megjelenítjük a szerkesztő űrlapot
            document.getElementById(field + '-form').style.display = 'block';
        }

        function cancelEdit(field) {
            // Elrejtjük a szerkesztő űrlapot
            document.getElementById(field + '-form').style.display = 'none';

            // Visszaállítjuk az értéket és az Edit gombot
            document.getElementById(field + '-value').style.display = 'inline';
            document.querySelector(`button[onclick="editField('${field}')"]`).style.display = 'inline';
        }

        function saveField(field) {
            // Az űrlap elküldése
            return true;
        }

        function submitAllForms() {
            // Az összes űrlap elküldése
            document.getElementById('username-form').submit();
            document.getElementById('email-form').submit();
            document.getElementById('password-form').submit();
            document.getElementById('profile_pic-form').submit();
        }

        function selectProfilePic(pic) {
            // Kijelölt kép beállítása
            const selectedPicInput = document.getElementById('selected-profile-pic');
            selectedPicInput.value = pic;

            // Kijelölt kép stílusának frissítése
            const images = document.querySelectorAll('.default-profile-pics img');
            images.forEach(img => {
                img.classList.remove('selected');
                if (img.src.includes(pic)) {
                    img.classList.add('selected');
                }
            });
        }

        // Változás figyelése
        document.addEventListener('DOMContentLoaded', function () {
            const saveButton = document.querySelector('.btn-primary');
            const forms = document.querySelectorAll('.edit-form');
            let isModified = false;
            let isProfilePicSelected = false;

            // Változás figyelése minden űrlap mezőben
            forms.forEach(form => {
                const inputs = form.querySelectorAll('input');
                inputs.forEach(input => {
                    input.addEventListener('input', () => {
                        isModified = true;
                        updateSaveButtonState();
                    });
                });
            });

            // Profilkép kiválasztásának figyelése
            const selectedProfilePicInput = document.getElementById('selected-profile-pic');

            if (selectedProfilePicInput) {
                selectedProfilePicInput.addEventListener('change', () => {
                    isProfilePicSelected = true;
                    updateSaveButtonState();
                });
            }

            // Mentés gomb eseménykezelője
            saveButton.addEventListener('click', (e) => {
                if (!isModified || !isProfilePicSelected) {
                    e.preventDefault(); // Megakadályozzuk az űrlap elküldését
                    alert('Nincsenek módosítások vagy profilkép kiválasztva a mentéshez.');
                }
            });

            // Mentés gomb állapotának frissítése
            function updateSaveButtonState() {
                if (isModified && isProfilePicSelected) {
                    saveButton.disabled = false; // Engedélyezzük a Mentés gombot
                } else {
                    saveButton.disabled = true; // Letiltjuk a Mentés gombot
                }
            }

            // Alapértelmezett letiltott állapot
            saveButton.disabled = true;
        });
    </script>

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
    </script>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>