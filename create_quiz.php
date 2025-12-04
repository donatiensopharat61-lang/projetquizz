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
        "questions" => []
    ];

    for ($i = 0; $i < count($_POST["question"]); $i++) {
        $quiz["questions"][] = [
            "text" => $_POST["question"][$i],
            "points" => intval($_POST["points"][$i]),
            "answers" => explode(";", $_POST["answers"][$i]),
            "correct" => $_POST["correct"][$i]
        ];
    }

    // Création dossier si nécessaire
    if (!is_dir("quiz")) mkdir("quiz");

    // Sauvegarde dans un fichier
    file_put_contents("data/quiz/" . $quiz["id"] . ".json", json_encode($quiz, JSON_PRETTY_PRINT));

    header("Location: dashboardentreprise.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Créer un Quiz - Quizzeo</title>

<style>
    body {
        font-family: "Segoe UI", sans-serif;
        background: #f4f4f4;
        margin: 0;
        padding: 0;
    }

    h1, h2 {
        font-weight: 600;
        color: #333;
        text-align: center;
    }

    .container {
        width: 900px;
        margin: 40px auto;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        animation: fadein 0.4s ease-out;
    }

    @keyframes fadein {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }

    label {
        font-weight: 600;
        margin-top: 10px;
        display: inline-block;
        color: #444;
    }

    input[type="text"], input[type="number"] {
        width: 100%;
        padding: 10px;
        border-radius: 8px;
        border: 1px solid #ccc;
        margin-bottom: 15px;
        transition: 0.2s;
    }

    input[type="text"]:focus, input[type="number"]:focus {
        border-color: #6ca0f6;
        outline: none;
        box-shadow: 0 0 5px rgba(100,150,255,0.4);
    }

    .btn {
        padding: 12px 20px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-size: 15px;
        font-weight: bold;
        transition: 0.2s;
    }

    .btn-primary {
        background: #6ca0f6;
        color: white;
    }

    .btn-primary:hover {
        background: #4f82dd;
    }

    .btn-add {
        background: #44c767;
        color:white;
    }

    .btn-add:hover {
        background: #36a557;
    }

    .question-block {
        background: #fafafa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid #eee;
    }

    hr {
        margin: 15px 0;
        border: none;
        border-bottom: 1px solid #ddd;
    }

</style>

</head>
<body>

<div class="container">
    <h1>Création d'un nouveau Quiz</h1>
    <hr>

    <form method="POST">

        <label>Titre du quiz</label>
        <input type="text" name="title" required>

        <h2>Questions</h2>

        <div id="questions"></div>

        <button type="button" class="btn btn-add" onclick="addQuestion()">+ Ajouter une question</button>
        <br><br>

        <button type="submit" class="btn btn-primary">Enregistrer le quiz</button>
    </form>
</div>

<script>
function addQuestion() {
    const container = document.getElementById('questions');

    const block = document.createElement('div');
    block.classList.add('question-block');

    block.innerHTML = `
        <label>Question</label>
        <input type="text" name="question[]" required>

        <label>Points attribués</label>
        <input type="number" name="points[]" min="1" required>

        <label>Réponses (séparées par ;)</label>
        <input type="text" name="answers[]" placeholder="ex: A;B;C;D" required>

        <label>Bonne réponse (ex: A)</label>
        <input type="text" name="correct[]" required>
    `;

    container.appendChild(block);
}
</script>

</body>
</html>
