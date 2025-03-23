<?php
session_start();
include '../config.php';

if ($_SESSION['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

// Új téma hozzáadása
if (isset($_POST['add_topic'])) {
    $subject_id = $_POST['subject_id'];
    $name = $_POST['name'];
    $conn->query("INSERT INTO topics (subject_id, name) VALUES ('$subject_id', '$name')");
    header("Location: manage_topics.php");
}

// Téma törlése
if (isset($_POST['delete_topic'])) {
    $topic_id = $_POST['topic_id'];
    $conn->query("DELETE FROM topics WHERE id='$topic_id'");
    header("Location: manage_topics.php");
}

$subjects = $conn->query("SELECT * FROM subjects");
$topics = $conn->query("SELECT topics.id, topics.name, subjects.name AS subject_name FROM topics JOIN subjects ON topics.subject_id = subjects.id");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Témák kezelése</title>
</head>
<body>
    <h2>Új téma hozzáadása</h2>
    <form method="post">
        <select name="subject_id">
            <?php while ($row = $subjects->fetch_assoc()) { ?>
                <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
            <?php } ?>
        </select>
        <input type="text" name="name" required>
        <button type="submit" name="add_topic">Hozzáadás</button>
    </form>

    <h2>Témák listája</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Téma</th>
            <th>Tantárgy</th>
            <th>Műveletek</th>
        </tr>
        <?php while ($row = $topics->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['subject_name']; ?></td>
            <td>
                <form method="post">
                    <input type="hidden" name="topic_id" value="<?php echo $row['id']; ?>">
                    <button type="submit" name="delete_topic">Törlés</button>
                </form>
            </td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>
