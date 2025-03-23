<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../config/config.php';

if (!$conn) {
    die("Adatbáziskapcsolat nem jött létre!");
}

// Téma azonosítójának lekérése
if (isset($_GET['id'])) {
    $topic_id = $_GET['id'];
} else {
    die("Téma azonosítója hiányzik!");
}

// Tantárgy azonosítójának lekérése a témához
$sql = "SELECT subject_id FROM topics WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Téma nem található!");
}

$topic = $result->fetch_assoc();
$subject_id = $topic['subject_id']; // Tantárgy azonosítója

// Ellenőrizzük, hogy van-e kvíz
$sql = "SELECT id FROM quizzes WHERE topic_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $error_message = "Nincs elérhető kvíz ehhez a témához.";
} else {
    // Van kvíz, így folytathatjuk
    $quiz = $result->fetch_assoc(); 
    $quiz_id = $quiz['id'];
}

// Kérdések lekérése
$questions = [];
$sql = "SELECT qq.*, qq.correct_answer FROM quiz_questions qq
        JOIN quizzes q ON qq.quiz_id = q.id
        WHERE q.topic_id = $topic_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }
} else {
    die("Nincsenek kérdések ehhez a kvízhez!");
}

// Beküldés után kiértékelés
$feedback = "";
$correct_count = 0;
$total_questions = count($questions);
$user_answers = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($questions as $question) {
        $question_id = $question['id'];
        $correct_answer = (int)$question['correct_answer']; // Helyes válasz sorszáma
        $user_choice = isset($_POST["answer_$question_id"]) ? (int)$_POST["answer_$question_id"] : null; // Felhasználó választása

        $user_answers[$question_id] = [
            'selected' => $user_choice,
            'correct' => $correct_answer
        ];

        if ($user_choice === $correct_answer) { // Szigorú összehasonlítás
            $correct_count++;
        }
    }

    if ($correct_count == $total_questions) {
        $feedback = "<div class='alert alert-success text-center'>🎉 Gratulálunk! Minden válasz helyes volt!</div>";
    } else {
        $feedback = "<div class='alert alert-danger text-center'>😢 Legközelebb biztos jobban megy! Próbáld újra.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kvíz</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .quiz-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fff;
            margin-top: 2rem;
        }
        .quiz-container h1 {
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .question {
            margin-bottom: 1.5rem;
        }
        .question p {
            font-size: 1.2rem;
            font-weight: bold;
        }
        .options label {
            display: block;
            margin-bottom: 0.5rem;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .options label.correct {
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .options label.incorrect {
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .back-link {
            margin-top: 2rem;
            text-align: center;
        }

        /* Sötét mód stílusok */
        body.dark-mode {
            background-color: #121212 !important;
            color: white !important;
        }
        .dark-mode .quiz-container {
            background-color: #1e1e1e;
            color: white;
            border-color: #333;
        }
        .dark-mode .options label {
            background-color: #333;
            border-color: #444;
            color: white;
        }
        .dark-mode .options label.correct {
            background-color: #4caf50;
            border-color: #45a049;
        }
        .dark-mode .options label.incorrect {
            background-color: #f44336;
            border-color: #e53935;
        }
        .dark-mode .btn-primary {
            background-color: #bb86fc;
            border-color: #bb86fc;
        }
        .dark-mode .btn-secondary {
            background-color: #3700b3;
            border-color: #3700b3;
        }
    </style>
</head>
<body class="bg-light" id="pageBody">
    <div class="quiz-container">
        <h1>Kvíz</h1>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-warning text-center"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <?php echo $feedback; ?>

        <form method="post">
            <?php foreach ($questions as $question): ?>
                <div class="question">
                    <p><?php echo $question['question']; ?></p>
                    <?php
                    $options = [$question['option1'], $question['option2'], $question['option3'], $question['option4']];
                    $question_id = $question['id'];
                    $correct_answer = (int)$question['correct_answer']; // Helyes válasz sorszáma
                    ?>

                    <div class="options">
                        <?php foreach ($options as $index => $option): ?>
                            <?php
                            $is_selected = isset($user_answers[$question_id]) && $user_answers[$question_id]['selected'] === ($index + 1); // Sorszám összehasonlítása
                            $is_correct = $correct_answer === ($index + 1); // Sorszám összehasonlítása
                            $class = '';

                            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                                if ($is_selected) {
                                    $class = $is_correct ? 'correct' : 'incorrect';
                                } elseif ($is_correct) {
                                    $class = 'correct';
                                }
                            }
                            ?>
                            <label class="<?php echo $class; ?>">
                                <input type="radio" name="answer_<?php echo $question_id; ?>" value="<?php echo $index + 1; ?>" <?php echo $is_selected ? 'checked' : ''; ?> required>
                                <?php echo $option; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <button type="submit" class="btn btn-primary w-100">Beküldés</button>
        </form>

        <div class="back-link">
            <a href="subjects.php?id=<?php echo $subject_id; ?>" class="btn btn-secondary">Vissza a témához</a>
        </div>

        <!-- Sötét mód váltó gomb -->
        <div class="text-center mt-4">
            <button id="toggleDarkMode" class="btn btn-secondary">Sötét mód</button>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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
</body>
</html>