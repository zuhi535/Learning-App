<?php
session_start();
include '../config.php';

if ($_SESSION['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

// Felhasználók listázása
$result = $conn->query("SELECT id, name, email, role FROM users");

// Felhasználó törlése
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    $conn->query("DELETE FROM users WHERE id='$user_id'");
    header("Location: manage_users.php");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Felhasználók kezelése</title>
</head>
<body>
    <h2>Felhasználók listája</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Név</th>
            <th>Email</th>
            <th>Szerep</th>
            <th>Műveletek</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['email']; ?></td>
            <td><?php echo $row['role']; ?></td>
            <td>
                <form method="post">
                    <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                    <button type="submit" name="delete_user">Törlés</button>
                </form>
            </td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>
