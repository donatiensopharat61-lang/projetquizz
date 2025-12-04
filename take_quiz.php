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
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($quiz["title"] ?? "Quiz") ?></title>
</head>
<body>

<h1><?= htmlspecialchars($quiz["title"] ?? "Quiz") ?></h1>

<form method="post" action="submit_quiz.php?id=<?= urlencode($_GET['id']) ?>">

<?php
$i = 0;
foreach ($quiz["questions"] as $q):

    // 🔥 1. NOM DE LA QUESTION (compatibilité)
    $label = $q["name"] ?? $q["text"] ?? "Question";

    // 🔥 2. RÉPONSES (compatibilité)
    if (is_array($q["answers"])) {
        // déjà un array → OK
        $answers = $q["answers"];
    } else {
        // ancienne version → answers = "A;B;C"
        $answers = explode(";", $q["answers"]);
    }

?>
    <h3><?= htmlspecialchars($label) ?></h3>

    <?php foreach ($answers as $a): ?>
        <label>
            <input type="radio" name="answer[<?= $i ?>]" value="<?= htmlspecialchars($a) ?>" required>
            <?= htmlspecialchars($a) ?>
        </label><br>
    <?php endforeach; ?>

    <hr>
<?php
$i++;
endforeach;
?>

<button type="submit">Envoyer</button>

</form>

</body>
</html>
