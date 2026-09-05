<?php
session_start();
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = [
        'id' => 1,
        'role' => 'admin',
        'name' => 'Administrateur Démo'
    ];
}

if ($_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo "Accès interdit — réservé aux administrateurs.";
    exit;
}

$dataUsers = __DIR__ . '/data/users';
$dataSchools = __DIR__ . '/data/ecoles';
$dataQuizzes = __DIR__ . '/data/quizzes';

foreach ([$dataUsers, $dataSchools, $dataQuizzes] as $dir) if (!is_dir($dir)) mkdir($dir, 0755, true);

function load_json_dir($dir) {
    $files = glob($dir . '/*.json');
    $out = [];
    foreach ($files as $f) {
        $j = json_decode(file_get_contents($f), true);
        if ($j) $out[] = $j;
    }
    return $out;
}

$users = load_json_dir($dataUsers);
$schools = load_json_dir($dataSchools);
$quizzes = load_json_dir($dataQuizzes);
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dashboard Admin — Quizzeo</title>
    <style>
        :root{--accent:#8b1ee0;--muted:#555}
        body{font-family:Inter,system-ui,Arial;margin:0;padding:0;background:#f3f4f7}
        header{background:white;padding:20px 28px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 2px 6px rgba(0,0,0,.08)}
        h1{margin:0;font-size:20px}
        .container{max-width:1200px;margin:30px auto;padding:0 20px}
        .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:20px}
        .card{background:white;border-radius:12px;padding:20px;box-shadow:0 6px 18px rgba(0,0,0,.05)}
        table{width:100%;border-collapse:collapse;margin-top:10px}
        th,td{padding:10px;text-align:left;border-bottom:1px solid #eee}
        th{color:var(--muted);font-size:14px}
        .btn{display:inline-block;background:var(--accent);color:white;padding:8px 12px;border-radius:8px;text-decoration:none}
        .btn.ghost{background:transparent;color:var(--accent);border:1px solid #d9c4ee}
        .count-box{background:#f7ecff;padding:12px;border-radius:10px;margin-top:10px;text-align:center}
    </style>
</head>
<body>
<header>
    <div>
        <h1>Quizzeo — Dashboard Administrateur</h1>
        <div style="font-size:13px;color:#777">Connecté en tant que : <?php echo htmlspecialchars($_SESSION['user']['name']); ?></div>
    </div>
</header>

<main class="container">

    <h2>Statistiques générales</h2>
    <div class="grid">
        <div class="card">
            <h3>Utilisateurs</h3>
            <div class="count-box"><strong><?php echo count($users); ?></strong><br>Comptes enregistrés</div>
        </div>
        <div class="card">
            <h3>Écoles</h3>
            <div class="count-box"><strong><?php echo count($schools); ?></strong><br>Écoles partenaires</div>
        </div>
        <div class="card">
            <h3>Quiz</h3>
            <div class="count-box"><strong><?php echo count($quizzes); ?></strong><br>Quiz présents</div>
        </div>
    </div>

    <h2 style="margin-top:40px">Gestion des utilisateurs</h2>
    <div class="card">
        <table>
            <thead><tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Actions</th></tr></thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="4">Aucun utilisateur trouvé.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($u['name']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td><?php echo htmlspecialchars($u['role']); ?></td>
                            <td>
                                <a class="btn ghost" href="edit_user.php?id=<?php echo $u['id']; ?>">Modifier</a>
                                <a class="btn" href="delete_user.php?id=<?php echo $u['id']; ?>">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <br>
        <a class="btn" href="create_user.php">+ Ajouter un utilisateur</a>
    </div>


    <h2 style="margin-top:40px">Gestion des écoles</h2>
    <div class="card">
        <table>
            <thead><tr><th>Nom</th><th>Ville</th><th>Actions</th></tr></thead>
            <tbody>
                <?php if (empty($schools)): ?>
                    <tr><td colspan="3">Aucune école enregistrée.</td></tr>
                <?php else: ?>
                    <?php foreach ($schools as $s): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($s['name']); ?></td>
                            <td><?php echo htmlspecialchars($s['city'] ?? '—'); ?></td>
                            <td>
                                <a class="btn ghost" href="edit_school.php?id=<?php echo $s['id']; ?>">Modifier</a>
                                <a class="btn" href="delete_school.php?id=<?php echo $s['id']; ?>">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <br>
        <a class="btn" href="create_school.php">+ Ajouter une école</a>
    </div>


    <h2 style="margin-top:40px">Gestion des quiz</h2>
    <div class="card">
        <table>
            <thead><tr><th>Titre</th><th>Créateur</th><th>Questions</th><th>Actions</th></tr></thead>
            <tbody>
                <?php if (empty($quizzes)): ?>
                    <tr><td colspan="4">Aucun quiz disponible.</td></tr>
                <?php else: ?>
                    <?php foreach ($quizzes as $q): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($q['title']); ?></td>
                            <td><?php echo htmlspecialchars($q['school_name'] ?? '—'); ?></td>
                            <td><?php echo isset($q['questions']) ? count($q['questions']) : 0; ?></td>
                            <td>
                                <a class="btn ghost" href="view_quiz.php?id=<?php echo $q['id']; ?>">Voir</a>
                                <a class="btn ghost" href="edit_quiz.php?id=<?php echo $q['id']; ?>">Modifier</a>
                                <a class="btn" href="delete_quiz.php?id=<?php echo $q['id']; ?>">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <br>
        <a class="btn" href="create_quiz.php">+ Créer un quiz</a>
    </div>

</main>

<footer style="text-align:center;margin:20px 0;color:#888;font-size:13px;">Quizzeo — Interface Administrateur</footer>
</body>
</html>