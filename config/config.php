<?php
$servername = "localhost";  // XAMPP esetén localhost
$username = "root";  // Alapértelmezett felhasználónév
$password = "";  // XAMPP esetén alapértelmezés szerint üres
$database = "voltizap";  // Az adatbázis neve

// Kapcsolódás az adatbázishoz
$conn = new mysqli($servername, $username, $password, $database);

// Kapcsolat ellenőrzése
if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}
?>
