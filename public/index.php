<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Kezdőlap</title>
</head>
<body>
    <h1>Üdvözöllek a tanulós webappban!</h1>
    <?php if (!isset($_SESSION['user_id'])) { ?>
        <a href="login.php">Bejelentkezés</a>
    <?php } else { ?>
        <a href="dashboard.php">Tovább a főoldalra</a>
    <?php } ?>
</body>
</html>
