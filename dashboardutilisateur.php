<?php
// dashboard_utilisateur.php
// Tableau de bord pour les utilisateurs (élèves)
session_start();

// --- Auth provisoire ---
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = [
        'id' => 101,
        'role' => 'utilisateur',
        'name' => 'Élève Demo'
    ];
}

if ($_SESSION['user']['role'] !== 'utilisateur') {
    http_response_code(403);
    echo "Accès interdit — réservé aux utilisateurs.";
    exit;
}

$dataDir = __DIR__ . '/data/quizzes';
if (!is_dir($dataDir)) mkdir($dataDir, 0755, true);

// Charger tous les quiz actifs
function load_active_quizzes($dir) {
    $files = glob($dir . '/quiz_*.json');
    $out = [];
    foreach ($files as $f) {
        $q = json_decode(file_get_contents($f), true);
        if ($q && !empty($q['active'])) $out[] = $q;
    }
    return $out;
}

$quizzes = load_active_quizzes($dataDir);
$userId = $_SESSION['user']['id'];

?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dashboard Utilisateur — Quizzeo</title>
    <style>
        :root{--accent:#149b40;--muted:#666}
        body{font-family:Inter,system-ui,Arial;margin:0;padding:0;background:#f5f7fa}
        header{background:white;padding:18px 28px;box-shadow:0 2px 6px rgba(20,20,40,.07);display:flex;align-items:center;justify-content:space-between}
        h1{margin:0;font-size:18px}
        .container{max-width:1000px;margin:28px auto;padding:0 18px}
        .card{background:white;padding:18px;border-radius:12px;box-shadow:0 6px 18px rgba(20,20,40,.04);margin-bottom:18px}
        table{width:100%;border-collapse:collapse}
        th,td{padding:10px 12px;text-align:left;border-bottom:1px solid #eee}
        th{color:var(--muted);font-size:13px}
        .btn{display:inline-block;padding:8px 12px;border-radius:8px;text-decoration:none;background:var(--accent);color:white}
        .btn.ghost{background:transparent;color:var(--accent);border:1px solid #d3ead9}
        .status{font-weight:600;padding:6px 8px;border-radius:8px;font-size:13px}
        .small{font-size:13px;color:#666}
    </style>
</head>
<body>
<header>
    <div>
        <h1>Quizzeo — Espace Utilisateur</h1>
        <div class="small">Connecté en tant que : <?php echo htmlspecialchars($_SESSION['user']['name']); ?></div>
    </div>
</header>

<main class="container">
    <section class="card">
        <h2>Quiz disponibles</h2>
        <p class="small">Voici les quiz accessibles pour vous.</p>
        <?php if (empty($quizzes)): ?>
            <p class="small">Aucun quiz disponible.</p>
        <?php else: ?>
            <table>
                <thead><tr>
                    <th>Titre</th>
                    <th>Questions</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr></thead>
                <tbody>
                <?php foreach ($quizzes as $q):
                    $answered = false;
                    if (!empty($q['responses'])) {
                        foreach ($q['responses'] as $resp) {
                            if (($resp['user_id'] ?? null) == $userId) $answered = true;
                        }
                    }
                ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($q['title']); ?></strong></td>
                        <td><?php echo isset($q['questions']) ? count($q['questions']) : 0; ?></td>
                        <td>
                            <?php if ($answered): ?>
                                <span class="status" style="background:#e8fbe8;color:#0f7d32">Terminé</span>
                            <?php else: ?>
                                <span class="status" style="background:#fff4d6;color:#b38400">À faire</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($answered): ?>
                                <a class="btn ghost" href="view_my_result.php?quiz=<?php echo $q['id']; ?>">Voir ma note</a>
                            <?php else: ?>
                                <a class="btn" href="start_quiz.php?id=<?php echo $q['id']; ?>">Commencer</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Mes résultats</h2>
        <?php
            $results = [];
            foreach ($quizzes as $q) {
                if (!empty($q['responses'])) {
                    foreach ($q['responses'] as $resp) {
                        if (($resp['user_id'] ?? null) == $userId) {
                            $results[] = [
                                'title' => $q['title'],
                                'score' => $resp['score'] ?? '?',
                                'max'   => $q['max_score'] ?? '?',
                                'id'    => $q['id']
                            ];
                        }
                    }
                }
            }
        ?>

        <?php if (empty($results)): ?>
            <p class="small">Vous n'avez encore terminé aucun quiz.</p>
        <?php else: ?>
            <table>
                <thead><tr><th>Quiz</th><th>Note</th><th>Détails</th></tr></thead>
                <tbody>
                    <?php foreach ($results as $r): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r['title']); ?></td>
                            <td><?php echo htmlspecialchars($r['score']); ?> / <?php echo htmlspecialchars($r['max']); ?></td>
                            <td><a class="btn ghost" href="view_my_result.php?quiz=<?php echo $r['id']; ?>">Voir</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

</main>
<footer style="text-align:center;color:#999;margin:18px 0">Quizzeo — Espace utilisateur</footer>
</body>
</html>
