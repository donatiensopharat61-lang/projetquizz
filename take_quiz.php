<?php
session_start();

if (!isset($_GET["id"])) die("Quiz manquant");

$path = "data/quiz/" . $_GET["id"] . ".json";

if (!file_exists($path)) die("Quiz introuvable");

$quiz = json_decode(file_get_contents($path), true);
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $quiz["title"] ?></title>
</head>
<body>

<h2><?= $quiz["title"] ?></h2>

<form method="POST" action="submitQuizz.php">
    <input type="hidden" name="quiz_id" value="<?= $quiz["id"] ?>">

    <?php foreach ($quiz["questions"] as $i => $q): ?>
        <h3><?= $q["name"] ?> (<?= $q["points"] ?> pts)</h3>

        <?php
        $answers = explode(";", $q["answers"]);
        foreach ($answers as $a):
        ?>
            <label>
                <input type="radio" name="answer[<?= $i ?>]" value="<?= $a ?>" required>
                <?= $a ?>
            </label><br>
        <?php endforeach; ?>

        <hr>
    <?php endforeach; ?>

    <button type="submit">Envoyer</button>

</form>
</body>
</html>
