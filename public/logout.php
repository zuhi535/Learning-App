<?php
session_start();

// A session megsemmisítése
session_unset();
session_destroy();

// Átirányítás a bejelentkezési oldalra
header("Location: login.php");
exit();
?>