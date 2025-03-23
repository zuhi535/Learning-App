<?php
session_start();
include '../config.php';

if ($_SESSION['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

// Új tantárgy hozzáadása
if (isset($_POST['add_subject'])) {
    $name = $_POST['name'];
    $conn->query("INSERT INTO subjects (name) VALUES ('$name')");
    header("Location: manage_subjects.php");
}

// Tantárgy törlése
if (isset($_POST['delete_subject'])) {
    $subject_id = $_POST['subject_id'];
    $conn->query("DELETE FROM subjects WHERE id='$subject_id'");
    header("Location: manage_subjects.php");
}

$result = $conn->query("SELECT * FROM subjects");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Tantárgyak kezelése</title>
</head>
<body>
    <h2>Új tantárgy hozzáadása</h2>
    <form method="post">
        <input type="text" name="name" required>
        <button type="submit" name="add_subject">Hozzáadás</button>
    </form>

    <h2>Tantárgyak listája</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Név</th>
            <th>Műveletek</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['name']; ?></td>
            <td>
                <form method="post">
                    <input type="hidden" name="subject_id" value="<?php echo $row['id']; ?>">
                    <button type="submit" name="delete_subject">Törlés</button>
                </form>
            </td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>
