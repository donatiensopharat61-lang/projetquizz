<?php
session_start();

if (!isset($_GET["id"])) die("ID du quiz manquant");

$path = "data/quiz/" . $_GET["id"] . ".json";

if (!file_exists($path)) die("Quiz introuvable");

$quiz = json_decode(file_get_contents($path), true);

// Mise à jour
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $quiz["title"] = $_POST["title"];
    $quiz["questions"] = [];

    foreach ($_POST["question"] as $i => $q) {
        $quiz["questions"][] = [
            "name" => $q,
            "points" => $_POST["points"][$i],
            "answers" => $_POST["answers"][$i],
            "correct" => $_POST["correct"][$i]
        ];
    }

    file_put_contents($path, json_encode($quiz, JSON_PRETTY_PRINT));

    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Modifier Quiz</title>
</head>
<body>

<h2>Modifier le quiz : <?= $quiz["title"] ?></h2>

<form method="POST">
    <label>Titre : </label>
    <input type="text" name="title" value="<?= $quiz["title"] ?>" required><br><br>

    <div id="questions">
        <?php foreach ($quiz["questions"] as $i => $q): ?>
            <div>
                <h4>Question <?= $i + 1 ?></h4>
                <input type="text" name="question[]" value="<?= $q["name"] ?>" required><br>
                <input type="number" name="points[]" value="<?= $q["points"] ?>" required><br>
                <input type="text" name="answers[]" value="<?= $q["answers"] ?>" required><br>
                <input type="text" name="correct[]" value="<?= $q["correct"] ?>" required><br>
                <hr>
            </div>
        <?php endforeach ?>
    </div>

    <button type="button" onclick="addQuestion()">+ Ajouter une question</button>
    <br><br>
    <button type="submit">Modifier</button>
</form>

<script>
function addQuestion() {
    const c = document.getElementById("questions");
    const i = c.children.length;

    c.innerHTML += `
        <div>
            <h4>Question ${i + 1}</h4>
            <input type="text" name="question[]" required><br>
            <input type="number" name="points[]" required><br>
            <input type="text" name="answers[]" required><br>
            <input type="text" name="correct[]" required><br>
            <hr>
        </div>
    `;
}
</script>

</body>
</html>
