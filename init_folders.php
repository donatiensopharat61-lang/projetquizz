<?php

$paths = [
    "data",
    "data/quiz"
];

foreach ($paths as $path) {

    if (!is_dir($path)) {
        if (mkdir($path, 0777, true)) {
            echo "Dossier créé : $path<br>";
        } else {
            echo "❌ Impossible de créer : $path<br>";
        }
    } else {
        echo "✔ Le dossier existe déjà : $path<br>";
    }

    if (chmod($path, 0777)) {
        echo "Permissions mises à 777 sur : $path<br>";
    } else {
        echo "⚠ Impossible de changer les permissions sur : $path<br>";
    }

    echo "<hr>";
}

if (is_dir("data/quiz")) {
    echo "<h3 style='color:green'>Tout est prêt ! Les quiz seront correctement stockés.</h3>";
} else {
    echo "<h3 style='color:red'>Erreur : le dossier data/quiz n'existe toujours pas.</h3>";
}
?>
