<?php
session_start();

if (!isset($_GET['id'])) die("Quiz manquant.");

$quiz_id = $_GET['id'];
$path = "data/quiz/quiz_" . $quiz_id . ".json";

if (!file_exists($path)) die("Quiz introuvable.");

$quiz = json_decode(file_get_contents($path), true);
$quiz['status'] = "terminé";
file_put_contents($path, json_encode($quiz, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));

header("Location: dashboardecole.php");
exit;
