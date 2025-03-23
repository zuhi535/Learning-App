<?php
session_start();

// Admin bejelentkezés ellenőrzése
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include '../config/config.php'; // Adatbáziskapcsolat

// Felhasználók lekérése az adatbázisból
$users = [];
$sql = "SELECT id, username, email, birthdate, gender, role, streak FROM users";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Tárgyak lekérése az adatbázisból
$subjects = [];
$sql = "SELECT id, name FROM subjects";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }
}

// Témakörök lekérése az adatbázisból
$topics = [];
$sql = "SELECT id, subject_id, title, description, image FROM topics";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $topics[] = $row;
    }
}

// Kvízek lekérése az adatbázisból
$quizzes = [];
$sql = "SELECT id, topic_id, title, description, image FROM quizzes";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $quizzes[] = $row;
    }
}

// Felhasználó törlése
if (isset($_GET['delete_user'])) {
    $user_id = $_GET['delete_user'];
    $sql = "DELETE FROM users WHERE id='$user_id'";
    if ($conn->query($sql) === TRUE) {
        echo "Felhasználó törölve!";
        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "Hiba a törlés során: " . $conn->error;
    }
}

// Tárgy törlése (egyesével)
if (isset($_GET['delete_subject'])) {
    $subject_id = $_GET['delete_subject'];
    $sql = "DELETE FROM subjects WHERE id='$subject_id'";
    if ($conn->query($sql) === TRUE) {
        echo "Tárgy törölve!";
        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "Hiba a tárgy törlése során: " . $conn->error;
    }
}

// Tárgyak tömeges törlése
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_selected_subjects'])) {
    if (!empty($_POST['subject_ids'])) {
        $subject_ids = implode(",", $_POST['subject_ids']);
        $sql = "DELETE FROM subjects WHERE id IN ($subject_ids)";
        if ($conn->query($sql) === TRUE) {
            echo "Kijelölt tárgyak törölve!";
            header("Location: admin_dashboard.php");
            exit();
        } else {
            echo "Hiba a tárgyak törlése során: " . $conn->error;
        }
    } else {
        echo "Nincsenek kijelölt tárgyak!";
    }
}

// Témakör törlése (egyesével)
if (isset($_GET['delete_topic'])) {
    $topic_id = $_GET['delete_topic'];
    $sql = "DELETE FROM topics WHERE id='$topic_id'";
    if ($conn->query($sql) === TRUE) {
        echo "Témakör törölve!";
        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "Hiba a témakör törlése során: " . $conn->error;
    }
}

// Témakörök tömeges törlése
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_selected_topics'])) {
    if (!empty($_POST['topic_ids'])) {
        $topic_ids = implode(",", $_POST['topic_ids']);
        $sql = "DELETE FROM topics WHERE id IN ($topic_ids)";
        if ($conn->query($sql) === TRUE) {
            echo "Kijelölt témakörök törölve!";
            header("Location: admin_dashboard.php");
            exit();
        } else {
            echo "Hiba a témakörök törlése során: " . $conn->error;
        }
    } else {
        echo "Nincsenek kijelölt témakörök!";
    }
}

// Kvíz törlése (egyesével)
if (isset($_GET['delete_quiz'])) {
    $quiz_id = $_GET['delete_quiz'];
    $sql = "DELETE FROM quizzes WHERE id='$quiz_id'";
    if ($conn->query($sql) === TRUE) {
        echo "Kvíz törölve!";
        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "Hiba a kvíz törlése során: " . $conn->error;
    }
}

// Kvízek tömeges törlése
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_selected_quizzes'])) {
    if (!empty($_POST['quiz_ids'])) {
        $quiz_ids = implode(",", $_POST['quiz_ids']);
        $sql = "DELETE FROM quizzes WHERE id IN ($quiz_ids)";
        if ($conn->query($sql) === TRUE) {
            echo "Kijelölt kvízek törölve!";
            header("Location: admin_dashboard.php");
            exit();
        } else {
            echo "Hiba a kvízek törlése során: " . $conn->error;
        }
    } else {
        echo "Nincsenek kijelölt kvízek!";
    }
}

// Új tárgy hozzáadása
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_subject'])) {
    $subject_name = $_POST['subject_name'];
    $sql = "INSERT INTO subjects (name) VALUES ('$subject_name')";
    if ($conn->query($sql) === TRUE) {
        echo "Tárgy sikeresen hozzáadva!";
        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "Hiba a tárgy hozzáadása során: " . $conn->error;
    }
}

// Új témakör hozzáadása
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_topic'])) {
    $subject_id = $_POST['subject_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $image = '';

    // Kép feltöltése
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Mappa létrehozása, ha nem létezik
        }
        $target_file = $target_dir . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image = basename($_FILES['image']['name']);
        } else {
            echo "Hiba a fájl feltöltése során!";
        }
    }

    // Adatellenőrzés
    if (empty($title) || empty($description)) {
        echo "A cím és a leírás nem lehet üres!";
    } else {
        $sql = "INSERT INTO topics (subject_id, title, description, image) VALUES ('$subject_id', '$title', '$description', '$image')";
        if ($conn->query($sql) === TRUE) {
            echo "Témakör sikeresen hozzáadva!";
            header("Location: admin_dashboard.php");
            exit();
        } else {
            echo "Hiba a témakör hozzáadása során: " . $conn->error;
        }
    }
}

// Új kvíz hozzáadása
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_quiz'])) {
    $topic_id = $_POST['topic_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $image = '';

    // Kép feltöltése
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Mappa létrehozása, ha nem létezik
        }
        $target_file = $target_dir . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image = basename($_FILES['image']['name']);
        } else {
            echo "Hiba a fájl feltöltése során!";
        }
    }

    // Adatellenőrzés
    if (empty($title) || empty($description)) {
        echo "A cím és a leírás nem lehet üres!";
    } else {
        $sql = "INSERT INTO quizzes (topic_id, title, description, image) VALUES ('$topic_id', '$title', '$description', '$image')";
        if ($conn->query($sql) === TRUE) {
            echo "Kvíz sikeresen hozzáadva!";
            header("Location: admin_dashboard.php");
            exit();
        } else {
            echo "Hiba a kvíz hozzáadása során: " . $conn->error;
        }
    }
}

// Új kvíz kérdés hozzáadása
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_quiz_question'])) {
    $quiz_id = $_POST['quiz_id'];
    $question = $_POST['question'];
    $correct_answer = $_POST['correct_answer'];
    $option1 = $_POST['option1'];
    $option2 = $_POST['option2'];
    $option3 = $_POST['option3'];
    $option4 = $_POST['option4'];

    // Adatellenőrzés
    if (empty($question) || empty($correct_answer) || empty($option1) || empty($option2) || empty($option3) || empty($option4)) {
        echo "Minden mező kitöltése kötelező!";
    } else {
        $sql = "INSERT INTO quiz_questions (quiz_id, question, correct_answer, option1, option2, option3, option4) 
                VALUES ('$quiz_id', '$question', '$correct_answer', '$option1', '$option2', '$option3', '$option4')";
        if ($conn->query($sql) === TRUE) {
            echo "Kvíz kérdés sikeresen hozzáadva!";
            header("Location: admin_dashboard.php");
            exit();
        } else {
            echo "Hiba a kvíz kérdés hozzáadása során: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Felület</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .section {
            margin-bottom: 2rem;
            padding: 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .section h2 {
            margin-bottom: 1rem;
        }
        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>
<body class="container mt-4">
    <h1 class="text-center mb-4">Üdvözöljük az admin felületen!</h1>

      <!-- Felhasználók listázása -->
      <div class="section">
    <h2>Regisztrált Felhasználók</h2>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Felhasználónév</th>
                    <th>E-mail</th>
                    <th>Születési dátum</th>
                    <th>Neme</th>
                    <th>Szerepkör</th>
                    <th>Streak</th>
                    <th>Pontszám</th> <!-- Új oszlop -->
                    <th>Műveletek</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo $user['username']; ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td><?php echo $user['birthdate']; ?></td>
                        <td><?php echo $user['gender']; ?></td>
                        <td><?php echo $user['role']; ?></td>
                        <td><?php echo $user['streak']; ?></td>
                        <td><?php echo isset($user['score']) ? $user['score'] : 0; ?></td> <!-- Új mező megjelenítése -->
                        <td>
                            <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning">Szerkesztés</a>
                            <a href="admin_dashboard.php?delete_user=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Biztosan törölni szeretné?')">Törlés</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

    <!-- Új tárgy hozzáadása -->
    <div class="section">
        <h2>Új Tárgy Hozzáadása</h2>
        <form method="post" class="mb-3">
            <div class="mb-3">
                <input type="text" name="subject_name" class="form-control" placeholder="Tárgy neve" required>
            </div>
            <button type="submit" name="add_subject" class="btn btn-success">Hozzáadás</button>
        </form>
    </div>

    <!-- Tárgyak listázása -->
    <div class="section">
        <h2>Tárgyak</h2>
        <form method="post" action="admin_dashboard.php">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Név</th>
                            <th>Műveletek</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subjects as $subject): ?>
                            <tr>
                                <td><?php echo $subject['id']; ?></td>
                                <td><?php echo $subject['name']; ?></td>
                                <td>
                                    <input type="checkbox" name="subject_ids[]" value="<?php echo $subject['id']; ?>" class="form-check-input">
                                    <a href="admin_dashboard.php?delete_subject=<?php echo $subject['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Biztosan törölni szeretné?')">Törlés</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <button type="submit" name="delete_selected_subjects" class="btn btn-danger">Kijelölt tárgyak törlése</button>
        </form>
    </div>

    <!-- Témakörök és kvízek listázása -->
    <div class="section">
        <h2>Témakörök és Kvízek</h2>
        <form method="post" action="admin_dashboard.php">
            <?php foreach ($subjects as $subject): ?>
                <h3><?php echo $subject['name']; ?></h3>
                <ul class="list-group">
                    <?php foreach ($topics as $topic): ?>
                        <?php if ($topic['subject_id'] == $subject['id']): ?>
                            <li class="list-group-item">
                                <strong><?php echo $topic['title']; ?></strong><br>
                                <?php echo $topic['description']; ?><br>
                                <?php if ($topic['image']): ?>
                                    <img src="../uploads/<?php echo $topic['image']; ?>" alt="Témakör képe" width="100" class="img-thumbnail">
                                <?php endif; ?>
                                <a href="admin_dashboard.php?delete_topic=<?php echo $topic['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Biztosan törölni szeretné?')">Törlés</a>
                                <h4 class="mt-3">Kvízek</h4>
                                <ul class="list-group">
                                    <?php foreach ($quizzes as $quiz): ?>
                                        <?php if ($quiz['topic_id'] == $topic['id']): ?>
                                            <li class="list-group-item">
                                                <input type="checkbox" name="quiz_ids[]" value="<?php echo $quiz['id']; ?>" class="form-check-input">
                                                <strong><?php echo $quiz['title']; ?></strong><br>
                                                <?php echo $quiz['description']; ?><br>
                                                <?php if ($quiz['image']): ?>
                                                    <img src="../uploads/<?php echo $quiz['image']; ?>" alt="Kvíz képe" width="100" class="img-thumbnail">
                                                <?php endif; ?>
                                                <a href="admin_dashboard.php?delete_quiz=<?php echo $quiz['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Biztosan törölni szeretné?')">Törlés</a>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                                <button type="submit" name="delete_selected_quizzes" class="btn btn-danger mt-2">Kijelölt kvízek törlése</button>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            <?php endforeach; ?>
        </form>
    </div>

    <!-- Új témakör hozzáadása -->
    <div class="section">
        <h2>Új Témakör Hozzáadása</h2>
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <select name="subject_id" class="form-select" required>
                    <option value="">Válassz tárgyat</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo $subject['id']; ?>"><?php echo $subject['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <input type="text" name="title" class="form-control" placeholder="Témakör címe" required>
            </div>
            <div class="mb-3">
                <textarea name="description" class="form-control" placeholder="Témakör leírása" required></textarea>
            </div>
            <div class="mb-3">
                <input type="file" name="image" class="form-control">
            </div>
            <button type="submit" name="add_topic" class="btn btn-success">Hozzáadás</button>
        </form>
    </div>

    <!-- Új kvíz hozzáadása -->
    <div class="section">
        <h2>Új Kvíz Hozzáadása</h2>
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <select name="topic_id" class="form-select" required>
                    <option value="">Válassz témakört</option>
                    <?php foreach ($topics as $topic): ?>
                        <option value="<?php echo $topic['id']; ?>"><?php echo $topic['title']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <input type="text" name="title" class="form-control" placeholder="Kvíz címe" required>
            </div>
            <div class="mb-3">
                <textarea name="description" class="form-control" placeholder="Kvíz leírása" required></textarea>
            </div>
            <div class="mb-3">
                <input type="file" name="image" class="form-control">
            </div>
            <button type="submit" name="add_quiz" class="btn btn-success">Hozzáadás</button>
        </form>
    </div>

    <!-- Új kvíz kérdés hozzáadása -->
    <div class="section">
        <h2>Új Kvíz Kérdés Hozzáadása</h2>
        <form method="post">
            <div class="mb-3">
                <select name="quiz_id" class="form-select" required>
                    <option value="">Válassz kvízt</option>
                    <?php foreach ($quizzes as $quiz): ?>
                        <option value="<?php echo $quiz['id']; ?>"><?php echo $quiz['title']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <textarea name="question" class="form-control" placeholder="Kérdés" required></textarea>
            </div>
            <div class="mb-3">
                <input type="text" name="correct_answer" class="form-control" placeholder="Helyes válasz" required>
            </div>
            <div class="mb-3">
                <input type="text" name="option1" class="form-control" placeholder="Válasz 1" required>
            </div>
            <div class="mb-3">
                <input type="text" name="option2" class="form-control" placeholder="Válasz 2" required>
            </div>
            <div class="mb-3">
                <input type="text" name="option3" class="form-control" placeholder="Válasz 3" required>
            </div>
            <div class="mb-3">
                <input type="text" name="option4" class="form-control" placeholder="Válasz 4" required>
            </div>
            <button type="submit" name="add_quiz_question" class="btn btn-success">Kérdés hozzáadása</button>
        </form>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>