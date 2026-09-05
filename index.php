<?php
session_start();
 
if (!isset($_SESSION['captcha'])) {
    $_SESSION['captcha'] = rand(1000, 9999);
}
 
$message = "";
 
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
 
    if ($email === "" || $password === "") {
        $message = "Veuillez remplir tous les champs.";
    } else {
        
        if (file_exists("users.json")) {
            $users = json_decode(file_get_contents("users.json"), true);
 
           if (isset($users[$email]) && password_verify($password, $users[$email]['password'])) {
    $_SESSION['user'] = $users[$email];
    switch ($_SESSION['user']['role']) {
        case 'admin':
            header("Location: dashboardadmin.php");
            break;
        case 'ecole':
            header("Location: dashboardecole.php");
            break;
        case 'entreprise':
            header("Location: dashboardentreprise.php");
            break;
        case 'utilisateur':
            header("Location: dashboardutilisateur.php");
            break;
        default:
            header("Location: index.php?error=role");
    }
    exit();

            } else {
                $message = "Identifiants incorrects.";
            }
        } else {
            $message = "Aucun utilisateur trouvé.";
        }
    }
}
 
if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $password = trim($_POST['password']);
    $captcha = trim($_POST['captcha']);
 
    if ($captcha != $_SESSION['captcha']) {
        $message = "Captcha incorrect.";
    } elseif ($name === "" || $email === "" || $password === "" || $role === "") {
        $message = "Veuillez remplir tous les champs.";
    } else {
        $users = file_exists("users.json")
            ? json_decode(file_get_contents("users.json"), true)
            : [];
 
        if (isset($users[$email])) {
            $message = "Ce compte existe déjà.";
        } else {
            $users[$email] = [
                "name" => $name,
                "email" => $email,
                "role" => $role,
                "password" => password_hash($password, PASSWORD_DEFAULT),
                "status" => "active"
            ];
 
            file_put_contents("users.json", json_encode($users, JSON_PRETTY_PRINT));
            $message = "Compte créé avec succès ! Vous pouvez vous connecter.";
            $_SESSION['captcha'] = rand(1000, 9999);
        }
    }
}
?>
 
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Quizzeo - Connexion / Inscription</title>
<style>
    body {
        font-family: Arial;
        background: #f4f4f4;
        display: flex;
        justify-content: center;
        padding-top: 50px;
    }
    .container {
        width: 450px;
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(0,0,0,0.2);
    }
    h2 { text-align: center; }
    form { margin-top: 20px; }
    input, select {
        width: 100%;
        padding: 10px;
        margin-top: 10px;
        border-radius: 6px;
        border: 1px solid #ccc;
    }
    button {
        width: 100%;
        padding: 12px;
        margin-top: 15px;
        background: #0066ff;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
    }
    button:hover { background: #004bcc; }
    .message { color: red; text-align: center; margin-top: 10px; }
</style>
</head>
<body>
 
<div class="container">
    <h2>Connexion</h2>
 
    <?php if ($message): ?>
        <p class="message"><?= $message ?></p>
    <?php endif; ?>
 
    <form method="POST">
        <input type="email" name="email" placeholder="Adresse email" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit" name="login">Se connecter</button>
    </form>
 
    <hr><h2>Inscription</h2>
 
    <form method="POST">
        <input type="text" name="name" placeholder="Nom complet" required>
        <input type="email" name="email" placeholder="Adresse email" required>
 
        <select name="role" required>
            <option value="">Choisir un rôle</option>
            <option value="ecole">École</option>
            <option value="entreprise">Entreprise</option>
        </select>
 
        <input type="password" name="password" placeholder="Mot de passe" required>
 
        <label>Captcha : <b><?= $_SESSION['captcha'] ?></b></label>
        <input type="text" name="captcha" placeholder="Recopiez le code" required>
 
        <button type="submit" name="register">Créer mon compte</button>
    </form>
</div>
 
</body>
</html>
 
</html>