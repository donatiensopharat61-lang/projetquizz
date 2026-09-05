<?php
session_start();

if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = [
        'id' => 600,
        'role' => 'entreprise',
        'name' => 'Entreprise Démo'
    ];
}

if ($_SESSION['user']['role'] !== 'entreprise') {
    http_response_code(403);
    echo "Accès interdit — réservé aux entreprises.";
    exit;
}

$dataSchools = __DIR__ . '/data/ecoles';
$dataQuiz = __DIR__ . '/data/quiz';
$dataAnswers = __DIR__ . '/data/answers';

foreach ([$dataSchools, $dataQuiz, $dataAnswers] as $dir) 
    if (!is_dir($dir)) mkdir($dir, 0755, true);

function load_dir($dir) {
    $files = glob($dir . '/*.json');
    $out = [];
    foreach ($files as $f) {
        $j = json_decode(file_get_contents($f), true);
        if ($j) $out[] = $j;
    }
    return $out;
}

$schools = load_dir($dataSchools);
$quiz = load_dir($dataQuiz);

?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dashboard Entreprise — Quizzeo</title>
    <style>
        :root{--accent:#e28a00;--muted:#666}
        body{font-family:Inter,system-ui,Arial;margin:0;padding:0;background:#f5f5f7}
        header{background:white;padding:18px 28px;box-shadow:0 2px 6px rgba(20,20,40,.07);display:flex;align-items:center;justify-content:space-between}
        h1{margin:0;font-size:20px}
        .container{max-width:1100px;margin:30px auto;padding:0 20px}
        .card{background:white;padding:20px;border-radius:12px;box-shadow:0 6px 18px rgba(20,20,40,.05);margin-bottom:20px}
        table{width:100%;border-collapse:collapse;margin-top:10px}
        th,td{padding:10px 12px;text-align:left;border-bottom:1px solid #eee}
        th{color:var(--muted)}
        .status{padding:6px 8px;border-radius:8px;font-size:13px;font-weight:600}
        .btn{display:inline-block;padding:8px 12px;border-radius:8px;background:var(--accent);color:white;text-decoration:none}
        .btn.ghost{background:transparent;color:var(--accent);border:1px solid #f2d6a6}
        .small{color:#777;font-size:13px}
    </style>
</head>
<body>
<header>
    <div>
        <h1>Quizzeo — Espace Entreprise</h1>
        <div class="small">Connecté en tant que : <?= htmlspecialchars($_SESSION['user']['name']); ?></div>
    </div>
    <div>
        <a class="btn" href="create_quiz.php">+ Nouveau quiz</a>
    </div>
</header>

<main class="container">

    <section class="card">
        <h2>Écoles partenaires</h2>
        <p class="small">Liste des écoles affiliées à votre entreprise.</p>
        <?php if (empty($schools)): ?>
            <p class="small">Aucune école enregistrée.</p>
        <?php else: ?>
            <table>
                <thead><tr><th>École</th><th>Ville</th><th>Quiz créés</th></tr></thead>
                <tbody>
                <?php foreach ($schools as $s): ?>
                    <?php
                        $countQuiz = 0;
                        foreach ($quiz as $q) 
                            if (($q['school_id'] ?? null) == $s['id']) 
                                $countQuiz++;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($s['name']); ?></td>
                        <td><?= htmlspecialchars($s['city'] ?? '—'); ?></td>
                        <td><?= $countQuiz; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>


    <section class="card">
        <h2>Résultats des élèves</h2>
        <p class="small">Sélectionnez un quiz pour consulter les performances globales.</p>

        <form method="get">
            <select name="quiz_id">
                <?php foreach ($quiz as $q): ?>

                    <?php  
                        $respFile = $dataAnswers . "/" . $q['id'] . ".json";
                        $respCount = file_exists($respFile)
                            ? count(json_decode(file_get_contents($respFile), true))
                            : 0;

                        $label = $q['title'];
                        if ($respCount > 0) $label .= " — réponses: " . $respCount;
                    ?>

                    <option value="<?= $q['id']; ?>" 
                        <?= (isset($_GET['quiz_id']) && $_GET['quiz_id']==$q['id']) ? "selected" : "" ?>>
                        <?= htmlspecialchars($label); ?>
                    </option>

                <?php endforeach; ?>
            </select>
            <button class="btn">Afficher</button>
        </form>

        <?php if (isset($_GET['quiz_id'])):
            $quizId = $_GET['quiz_id'];
            $target = null;

            foreach ($quiz as $q) 
                if ($q['id'] == $quizId) 
                    $target = $q;
        ?>

            <?php if (!$target): ?>
                <p class="small">Quiz introuvable.</p>
            <?php else: ?>
                <h3 style="margin-top:20px">Résultats — <?= htmlspecialchars($target['title']); ?></h3>

                <?php  
                    $respFile = $dataAnswers . "/" . $quizId . ".json";
                    $responses = [];

                    if (!empty($target['responses'])) {
                        $responses = $target['responses'];
                    } elseif (file_exists($respFile)) {
                        $responses = json_decode(file_get_contents($respFile), true);
                    }
                ?>

                <?php if (empty($responses)): ?>
                    <p class="small">Aucun élève n'a encore répondu.</p>
                <?php else: ?>
                    <table>
                        <thead><tr><th>Nom</th><th>Score</th><th>Date</th></tr></thead>
                        <tbody>
                            <?php foreach ($responses as $r): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['user'] ?? $r['name'] ?? "Anonyme"); ?></td>
                                    <td><?= htmlspecialchars($r['score']); ?></td>
                                    <td><?= htmlspecialchars($r['date'] ?? ""); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </section>

</main>

<footer style="text-align:center;color:#777;margin:20px 0;font-size:13px">
    Quizzeo — Interface Entreprise
</footer>

</body>
</html>
