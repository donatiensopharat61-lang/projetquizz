<?php
session_start();

if (isset($_GET["id"])) {
    $quiz_id = $_GET["id"];
} elseif (isset($_POST["quiz_id"])) {
    $quiz_id = $_POST["quiz_id"];
} else {
    die("Quiz introuvable (id manquant)");
}

$path = "data/quiz/$quiz_id.json";
$path_res = "data/answers/$quiz_id.json";

if (!file_exists($path)) die("Quiz introuvable");

$quiz = json_decode(file_get_contents($path), true);

if (!isset($_POST["answer"])) die("Aucune réponse fournie");

$score = 0;

foreach ($quiz["questions"] as $i => $q) {

    $correct = $q["correct"] ?? "";

    if (isset($_POST["answer"][$i]) && $_POST["answer"][$i] == $correct) {
        $score += intval($q["points"] ?? 1);
    }
}

$response = [
    "id" => uniqid("resp_"),
    "user" => $_SESSION["user"]["name"] ?? "Anonyme",
    "score" => $score,
    "date" => date("Y-m-d H:i:s"),
    "answers" => $_POST["answer"]
];

if (!is_dir("data/answers")) mkdir("data/answers");

$existing = [];
if (file_exists($path_res)) {
    $existing = json_decode(file_get_contents($path_res), true);
}

$existing[] = $response;

file_put_contents($path_res, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultat du Quiz</title>

    <style>
        body {
            background: #f6f8fb;
            font-family: Inter, Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        header {
            background: white;
            padding: 18px 28px;
            box-shadow: 0 2px 6px rgba(20,20,40,.06);
        }
        h1 {
            margin: 0;
            font-size: 20px;
        }
        .container {
            max-width: 600px;
            margin: 60px auto;
            padding: 20px;
        }
        .card {
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(20,20,40,.05);
            text-align: center;
        }
        .score {
            font-size: 28px;
            color: #2b6cb0;
            font-weight: bold;
            margin-top: 10px;
        }
        .btn {
            display: inline-block;
            margin-top: 25px;
            padding: 10px 18px;
            background: #2b6cb0;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            transition: 0.2s;
        }
        .btn:hover {
            background: #1d4f80;
        }
    </style>
</head>

<body>

<header>
    <h1>Quizzeo — Résultat</h1>
</header>

<div class="container">
    <div class="card">
        <h2>Merci !</h2>
        <p>Votre score est :</p>

        <div class="score">
            <?= htmlspecialchars($score) ?> points
        </div>

        <a href="dashboardecole.php" class="btn">Retour au tableau de bord</a>
    </div>
</div>

</body>
</html>
