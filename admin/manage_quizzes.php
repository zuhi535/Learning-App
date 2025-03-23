<?php
session_start();
include '../config.php';

if ($_SESSION['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

// Új kvíz hozzáadása
if (isset($_POST['add_quiz'])) {
    $topic_id = $_POST['topic_id'];
    $question = $_POST['question'];
    $answer = $_POST['answer'];
    $conn->query("INSERT INTO quizzes (topic_id, question, answer) VALUES ('$topic_id', '$question', '$answer')");
    header("Location: manage_quizzes.php");
}

// Kvíz törlése
if (isset($_POST['delete_quiz'])) {
    $quiz_id = $_POST['quiz_id'];
    $conn->query("DELETE FROM quizzes WHERE id='$quiz_id'");
    header("Location: manage_quizzes.php");
}

$topics = $conn->query("SELECT * FROM topics");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Kvízek kezelése</title>
</head>
<body>
    <h2>Új kvíz hozzáadása</h2>
    <form method="post">
        <select name="topic_id">
            <?php while ($row = $topics->fetch_assoc()) { ?>
                <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
            <?php } ?>
        </select>
        <input type="text" name="question" required>
        <input type="text" name="answer" required>
        <button type="submit" name="add_quiz">Hozzáadás</button>
    </form>
</body>
</html>
