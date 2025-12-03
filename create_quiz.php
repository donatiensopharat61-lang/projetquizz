<?php
session_start();

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

// Sauvegarde du quiz
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $quiz = [
        "id" => uniqid("quiz_"),
        "title" => $_POST["title"],
        "status" => "en_ecriture",
        "owner" => $_SESSION["user"]["id"],
        "questions" => []
    ];

    // Ajouter questions
    foreach ($_POST["question"] as $i => $q) {
        $quiz["questions"][] = [
            "name" => $q,
            "type" => "qcm", // pour école
            "points" => $_POST["points"][$i],
            "answers" => $_POST["answers"][$i],
            "correct" => $_POST["correct"][$i]
        ];
    }

    // Sauvegarde en fichier JSON
    if (!is_dir("data/quiz")) mkdir("data/quiz", 0777, true);

    file_put_contents("data/quiz/{$quiz['id']}.json", json_encode($quiz, JSON_PRETTY_PRINT));

    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Créer un Quiz</title>
</head>
<body>

<h2>Créer un nouveau quiz</h2>

<form method="POST">
    <label>Titre du quiz : </label>
    <input type="text" name="title" required><br><br>

    <div id="questions"></div>

    <button type="button" onclick="addQuestion()">+ Ajouter une question</button>
    <br><br>
    <button type="submit">Créer le quiz</button>
</form>

<script>
function addQuestion() {
    const container = document.getElementById("questions");
    const index = container.children.length;

    container.innerHTML += `
        <div>
            <h4>Question ${index + 1}</h4>
            <input type="text" name="question[]" placeholder="Texte de la question" required><br>
            <input type="number" name="points[]" placeholder="Points" required><br>

            <label>Réponses (séparées par ;)</label><br>
            <input type="text" name="answers[]" placeholder="ex: A;B;C;D" required><br>

            <label>Bonne réponse (ex: A)</label><br>
            <input type="text" name="correct[]" required>
            <hr>
        </div>
    `;
}
</script>

</body>
</html>
