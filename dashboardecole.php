<?php
// dashboard_ecole.php
// Minimal file-based dashboard for role: ecole
// Requirements: PHP 7+, writable 'data/quizzes' directory containing JSON files for quizzes
session_start();

// --- Simple auth stub (replace with real auth) ---
if (!isset($_SESSION['user'])) {
    // For demo purposes, auto-login a school user
    $_SESSION['user'] = [
        'id' => 1,
        'role' => 'ecole',
        'name' => 'Lycée Demo'
    ];
}

if ($_SESSION['user']['role'] !== 'ecole') {
    http_response_code(403);
    echo "Accès interdit — seul le rôle 'ecole' peut voir ce dashboard.";
    exit;
}

$dataDir = __DIR__ . '/data/quizzes';
if (!is_dir($dataDir)) mkdir($dataDir, 0755, true);

// Helper: load quizzes (files named quiz_<id>.json)
function load_quizzes($dir) {
    $files = glob($dir . '/quiz_*.json');
    $out = [];
    foreach ($files as $f) {
        $json = @file_get_contents($f);
        $q = json_decode($json, true);
        if ($q) $out[] = $q;
    }
    usort($out, function($a,$b){ return strcmp($a['title'] ?? '', $b['title'] ?? ''); });
    return $out;
}

$quizzes = load_quizzes($dataDir);

// Action handlers (toggle active)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = preg_replace('/[^0-9_\-]/', '', $_GET['id']);
    $path = $dataDir . "/quiz_{$id}.json";
    if (file_exists($path)) {
        $q = json_decode(file_get_contents($path), true);
        if ($_GET['action'] === 'toggle') {
            $q['active'] = empty($q['active']) ? 1 : 0;
            file_put_contents($path, json_encode($q, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
            exit;
        }
        if ($_GET['action'] === 'delete') {
            unlink($path);
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
            exit;
        }
    }
}

?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dashboard École — Quizzeo</title>
    <style>
        :root{--accent:#2b6cb0;--muted:#666}
        body{font-family:Inter, system-ui, Arial; margin:0; padding:0; background:#f6f8fb}
        header{background:white;padding:18px 28px;box-shadow:0 2px 6px rgba(20,20,40,.06);display:flex;align-items:center;justify-content:space-between}
        h1{margin:0;font-size:18px}
        .container{max-width:1100px;margin:28px auto;padding:0 18px}
        .card{background:white;padding:18px;border-radius:12px;box-shadow:0 6px 18px rgba(20,20,40,.04);margin-bottom:18px}
        table{width:100%;border-collapse:collapse}
        th,td{padding:10px 12px;text-align:left;border-bottom:1px solid #eee}
        th{color:var(--muted);font-size:13px}
        .btn{display:inline-block;padding:8px 12px;border-radius:8px;text-decoration:none;background:var(--accent);color:white}
        .btn.ghost{background:transparent;color:var(--accent);border:1px solid #e6eefc}
        .status{font-weight:600;padding:6px 8px;border-radius:8px;font-size:13px}
        .status.launched{background:#e6f6ff;color:#006db3}
        .status.draft{background:#fff7e6;color:#b37b00}
        .small{font-size:13px;color:#666}
        .actions a{margin-right:6px}
        .stats{display:flex;gap:18px}
        .stat-item{background:#f3f7ff;padding:12px;border-radius:10px;min-width:140px}
    </style>
</head>
<body>
<header>
    <div>
        <h1>Quizzeo — Dashboard École</h1>
        <div class="small">Connecté en tant que : <?php echo htmlspecialchars($_SESSION['user']['name']); ?></div>
    </div>
    <div>
        <a class="btn" href="create_quiz.php">+ Nouveau quiz</a>
    </div>
</header>

<main class="container">
    <section class="card">
        <h2>Résumé</h2>
        <?php
            $total = count($quizzes);
            $launched = 0; $finished = 0; $responses = 0;
            foreach ($quizzes as $q) {
                if (!empty($q['status']) && $q['status'] === 'lancé') $launched++;
                if (!empty($q['status']) && $q['status'] === 'terminé') $finished++;
                $responses += isset($q['responses']) ? count($q['responses']) : 0;
            }
        ?>
        <div class="stats" style="margin-top:12px">
            <div class="stat-item"><strong><?php echo $total; ?></strong><div class="small">Quiz créés</div></div>
            <div class="stat-item"><strong><?php echo $launched; ?></strong><div class="small">Quiz lancés</div></div>
            <div class="stat-item"><strong><?php echo $finished; ?></strong><div class="small">Quiz terminés</div></div>
            <div class="stat-item"><strong><?php echo $responses; ?></strong><div class="small">Réponses totales</div></div>
        </div>
    </section>

    <section class="card">
        <h2>Mes quiz</h2>
        <?php if (empty($quizzes)): ?>
            <p class="small">Aucun quiz pour le moment. Créez-en un pour commencer.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Statut</th>
                        <th>Questions</th>
                        <th>Réponses</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($quizzes as $q):
                    $id = $q['id'] ?? basename($q['file'] ?? '');
                    $status = $q['status'] ?? 'en écriture';
                    $nq = isset($q['questions']) ? count($q['questions']) : 0;
                    $nr = isset($q['responses']) ? count($q['responses']) : 0;
                ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($q['title'] ?? 'Untitled'); ?></strong>
                            <div class="small">Créé le <?php echo htmlspecialchars($q['created_at'] ?? '—'); ?></div>
                        </td>
                        <td>
                            <?php if ($status === 'lancé'): ?>
                                <div class="status launched">Lancé</div>
                            <?php elseif ($status === 'terminé'): ?>
                                <div class="status launched">Terminé</div>
                            <?php else: ?>
                                <div class="status draft">En écriture</div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $nq; ?></td>
                        <td><?php echo $nr; ?></td>
                        <td class="actions">
                            <a class="btn ghost" href="view_quiz.php?id=<?php echo urlencode($q['id']); ?>">Voir</a>
                            <a class="btn ghost" href="edit_quiz.php?id=<?php echo urlencode($q['id']); ?>">Éditer</a>
                            <a class="btn" href="?action=toggle&id=<?php echo urlencode($q['id']); ?>"><?php echo empty($q['active']) ? 'Activer' : 'Désactiver'; ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Quiz terminé — Notes élèves</h2>
        <p class="small">Sélectionnez un quiz terminé pour voir la liste des élèves et leurs notes.</p>
        <?php
            // quick selection form
            $finishedQuizzes = array_filter($quizzes, function($q){ return isset($q['status']) && $q['status'] === 'terminé'; });
        ?>
        <?php if (empty($finishedQuizzes)): ?>
            <p class="small">Aucun quiz terminé.</p>
        <?php else: ?>
            <form method="get">
                <select name="view_quiz_id">
                    <?php foreach ($finishedQuizzes as $q): ?>
                        <option value="<?php echo htmlspecialchars($q['id']); ?>" <?php if(isset($_GET['view_quiz_id']) && $_GET['view_quiz_id']==$q['id']) echo 'selected'; ?>><?php echo htmlspecialchars($q['title']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn">Afficher</button>
            </form>

            <?php if (isset($_GET['view_quiz_id'])):
                $id = $_GET['view_quiz_id'];
                $path = $dataDir . "/quiz_{$id}.json";
                if (file_exists($path)) {
                    $qd = json_decode(file_get_contents($path), true);
                    $responses = $qd['responses'] ?? [];
                } else {
                    $responses = [];
                }
            ?>
                <div style="margin-top:12px;">
                    <table>
                        <thead><tr><th>Élève</th><th>Note</th><th>Détails</th></tr></thead>
                        <tbody>
                            <?php if (empty($responses)): ?>
                                <tr><td colspan="3">Aucune réponse enregistrée.</td></tr>
                            <?php else: ?>
                                <?php foreach ($responses as $r): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($r['name'] ?? 'Anonyme'); ?></td>
                                        <td><?php echo isset($r['score']) ? htmlspecialchars($r['score']).' / '.htmlspecialchars($qd['max_score'] ?? '?') : '-'; ?></td>
                                        <td><a class="btn ghost" href="view_response.php?quiz=<?php echo urlencode($qd['id']); ?>&resp=<?php echo urlencode($r['id'] ?? ''); ?>">Voir</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>

    <footer style="text-align:center;color:#999;margin-top:18px">Prototype — Stockage en fichiers JSON dans <code>data/quizzes/</code></footer>
</main>

</body>
</html>
