<?php
// init_folders.php
// Script pour créer automatiquement les dossiers nécessaires

$paths = [
    "data",
    "data/quiz"
];

foreach ($paths as $path) {

    // Si le dossier n'existe pas, on le crée
    if (!is_dir($path)) {
        if (mkdir($path, 0777, true)) {
            echo "Dossier créé : $path<br>";
        } else {
            echo "❌ Impossible de créer : $path<br>";
        }
    } else {
        echo "✔ Le dossier existe déjà : $path<br>";
    }

    // Tenter de mettre les permissions
    if (chmod($path, 0777)) {
        echo "Permissions mises à 777 sur : $path<br>";
    } else {
        echo "⚠ Impossible de changer les permissions sur : $path<br>";
    }

    echo "<hr>";
}

// Vérification finale
if (is_dir("data/quiz")) {
    echo "<h3 style='color:green'>Tout est prêt ! Les quiz seront correctement stockés.</h3>";
} else {
    echo "<h3 style='color:red'>Erreur : le dossier data/quiz n'existe toujours pas.</h3>";
}
?>
