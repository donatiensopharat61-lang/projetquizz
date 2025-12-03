<?php
session_start();

$quiz_id = $_POST["quiz_id"];
$path = "data/quiz/$quiz_id.json";
$path_res = "data/answers/$quiz_id.json";

if (!file_exists($path)) die("Quiz introuvable");

$quiz = json_decode(file_get_contents($path), true);

if (!isset($_POST["answer"])) die("Aucune réponse fournie");

$score = 0;

foreach ($quiz["questions"] as $i => $q) {
    $userAns = $_POST["answer"][$i];
    if (trim($userAns) === trim($q["correct"])) {
        $score += $q["points"];
    }
}

$response = [
    "user" => $_SESSION["user"]["id"],
    "name" => $_SESSION["user"]["firstname"] . " " . $_SESSION["user"]["lastname"],
    "score" => $score,
    "answers" => $_POST["answer"],
    "date" => date("Y-m-d H:i")
];

if (!is_dir("data/answers")) mkdir("data/answers");

$existing = [];
if (file_exists($path_res)) {
    $existing = json_decode(file_get_contents($path_res), true);
}

$existing[] = $response;

file_put_contents($path_res, json_encode($existing, JSON_PRETTY_PRINT));

echo "<h2>Merci ! Votre score est : $score points</h2>";
echo "<a href='dashboard.php'>Retour</a>";
