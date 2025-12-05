<?php
// views/utilisateurs/connexion.php
// session_start(); // DÉCOMMENTÉ !

require "database/database.php";

$error = "";
$message="";

if (isset($_GET['message'])) {
    $message=$_GET['message'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $mdp   = $_POST['mdp'] ?? '';

    if (empty($login) || empty($mdp)) {
        $error = "Tous les champs sont obligatoires.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE login = ? AND etat = 'actif' ");
            $stmt->execute([$login]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // COMPARAISON DIRECTE sans password_verify
            if ($user && $mdp === $user['mdp']) {
                session_regenerate_id(true);

                // Décommente cette ligne
                $_SESSION['utilisateur_id'] = $user['utilisateur_id'];
                $_SESSION['nom_prenom']     = $user['nom_prenom'];
                $_SESSION['login']          = $user['login'];
                $_SESSION['role']           = $user['role'];
                $_SESSION['photo']          = $user['photo'];
                $_SESSION['type_photo']     = $user['type_photo'];

                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
                $host = $_SERVER['HTTP_HOST'];
                $dir = dirname($_SERVER['PHP_SELF']);
                $base = ($dir !== '/' && substr($dir, -1) === '/') ? substr($dir, 0, -1) : $dir;
                $redirect_url = $protocol . $host . $base . '/utilisateur/dashboard';
                ?>
                <script type="text/javascript">
                    document.location.replace("<?= $redirect_url ?>");
                </script>
                <?php
                exit();

            } else {
                $error = "Login ou mot de passe incorrect.";
            }
        } catch (Exception $e) {
            $error = "Erreur système. Réessayez plus tard.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Soutra+ | Connexion</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .login-page {
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .login-box { width: 100%; max-width: 400px; }
        .card {
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            border: 1px solid #e0e0e0;
        }
        .card-header {
            background: #007bff;
            color: white;
            text-align: center;
            padding: 2rem 1rem;
        }
        .card-header img {
            width: 80px; height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            margin-bottom: 1rem;
        }
        .input-group-text { background: #007bff; color: white; border: none; }
        .form-control { border-radius: 0.5rem; }
        .btn-primary {
            background: #007bff;
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem;
            font-weight: 600;
        }
        .btn-primary:hover { background: #0056b3; }
        .alert { border-radius: 0.5rem; font-size: 0.9rem; }
    </style>
</head>
<body class="hold-transition login-page">

<div class="login-box">
    <div class="card">
        <div class="card-header">
            <img src="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/assets/logo.png" alt="Logo">
            <h4 class="mb-0"><b>Soutra</b>+</h4>
            <small>Gestion Hôtelière</small>
        </div>

        <div class="card-body p-4">
            <p class="login-box-msg text-center mb-4">Connectez-vous à votre compte</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

                        <?php if (!empty($message)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="input-group mb-3">
                    <input type="text" name="login" class="form-control" placeholder="Login" value="<?= htmlspecialchars($login ?? '') ?>" required autofocus>
                    <span class="input-group-text"><span class="fas fa-user"></span></span>
                </div>

                <div class="input-group mb-4">
                    <input type="password" name="mdp" class="form-control" placeholder="Mot de passe" required>
                    <span class="input-group-text"><span class="fas fa-lock"></span></span>
                </div>

                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-block w-100">
                            Se connecter
                        </button>
                        <br><br>
                        Vous n'avez pas de compte ? 
                        <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/inscription">
                            Inscrivez-vous
                        </a>
                    </div>
                </div>
            </form>

            <div class="text-center mt-3">
                <small class="text-muted">
                    © 2025 <a href="#">Soutra+</a>. Tous droits réservés.
                </small>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>