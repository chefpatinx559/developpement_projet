
<?php
require "database/database.php";
$message = '';
$alert_type = 'danger';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id        = trim($_POST['utilisateur_id']);
    $nom_prenom = trim($_POST['nom_prenom'] ?? '');
    $login      = trim($_POST['login'] ?? '');
    $mdp        = $_POST['mdp'] ?? '';
    $telephone  = trim($_POST['telephone'] ?? '');

    // $photo = $type_photo = null;
    // if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    //     $allowed = ['image/jpeg', 'image/png', 'image/gif'];
    //     if (in_array($_FILES['photo']['type'], $allowed) && $_FILES['photo']['size'] <= 2*1024*1024) {
    //         $photo      = file_get_contents($_FILES['photo']['tmp_name']);
    //         $type_photo = $_FILES['photo']['type'];
    //     } else {
    //         $message = "Photo invalide ou trop lourde.";
    //     }
    // }

    if (empty($nom_prenom) || empty($login) || empty($mdp)) {
        $message = "Champs obligatoires manquants.";
    } elseif (strlen($mdp) < 6) {
        $message = "Mot de passe : 6 caractères minimum.";
    } else {
        try {
    $check = $pdo->prepare("SELECT 1 FROM utilisateurs WHERE login = ?");
    $check->execute([$login]);

    if ($check->fetch()) {
        $message = "Ce login existe déjà.";
    } else {

       $stmt = $pdo->prepare("INSERT INTO utilisateurs 
    (nom_prenom, login, telephone, mdp, role, etat)
    VALUES (?, ?, ?, ?, 'Superviseur', 'actif')");
$stmt->execute([$nom_prenom, $login, $telephone, $mdp]);


        $message = "Inscription réussie !";
        $alert_type = 'success';
        header('location:/utilisateur/connexion?message='.$message);
        exit;
    }
}
catch (Exception $e) {
    $message = "Erreur système.";
}

    }
}

function base_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $dir = dirname($_SERVER['PHP_SELF']);
    $dir = $dir === '/' ? '' : rtrim($dir, '/');
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $dir;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Soutra+ | Inscription</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html,body{height:100%;margin:0;overflow:hidden;background:#f8f9fa}
        .login-page{display:flex;align-items:center;justify-content:center;min-height:100vh;padding:10px}
        .card{max-width:380px;width:100%;border-radius:12px;box-shadow:0 8px 25px rgba(0,0,0,.12);border:1px solid #e0e0e0;overflow:hidden}
        .card-header{background:#007bff;color:#fff;text-align:center;padding:14px 10px 12px}
        .card-header img{width:58px;height:58px;border:3px solid #fff;border-radius:50%;object-fit:cover}
        .card-header h4{font-size:1.35rem;margin:8px 0 2px}
        .card-header small{font-size:.82rem;opacity:.9}
        .card-body{padding:16px 20px 18px}
        .login-box-msg{font-size:1rem;margin:0 0 12px;text-align:center}
        .input-group{margin-bottom:9px}
        .input-group-text{background:#007bff;color:#fff;border:none;width:42px;justify-content:center}
        .form-control{height:42px;font-size:.94rem;border-radius:8px}
        .btn-primary{background:#007bff;border:none;border-radius:8px;padding:10px;font-weight:600;height:44px}
        .btn-primary:hover{background:#0056b3}
        .preview-img{width:72px;height:72px;border:3px solid #007bff;border-radius:50%;margin:6px auto 4px;display:block}
        .photo-label{margin-top:4px}
        .text-muted small{font-size:.76rem}
        .alert{font-size:.84rem;padding:6px 10px;margin-bottom:10px}
        .footer-text{margin-top:10px;font-size:.76rem}
    </style>
</head>
<body class="login-page">
<div class="card">
    <div class="card-header">
        <img src="<?= base_url() ?>/assets/logo.png" alt="Logo">
        <h4 class="mb-0"><b>Soutra</b>+</h4>
        <small>Gestion Hôtelière</small>
    </div>

    <div class="card-body">
        <p class="login-box-msg">Créer un compte Superviseur</p>

        <?php if ($message): ?>
            <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="input-group">
                <input type="text" name="nom_prenom" class="form-control" placeholder="Nom et prénom" 
                       value="<?= htmlspecialchars($_POST['nom_prenom']??'') ?>" required>
                <span class="input-group-text"><i class="fas fa-user"></i></span>
            </div>


              <div class="input-group">
                <input type="text" name="login" class="form-control" placeholder="Login"
                       value="<?= htmlspecialchars($_POST['login']??'') ?>" required>
                <span class="input-group-text"><i class="fas fa-at"></i></span>
            </div>

            <div class="input-group">
                <input type="tel" name="telephone" class="form-control" placeholder="Téléphone (optionnel)"
                       value="<?= htmlspecialchars($_POST['telephone']??'') ?>">
                <span class="input-group-text"><i class="fas fa-phone"></i></span>
            </div>
      

            <div class="input-group">
                <input type="password" name="mdp" class="form-control" placeholder="Mot de passe (≥6 car.)" required>
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
            </div>

          <!--   <div class="text-center">
                <img id="preview" src="https://via.placeholder.com/72/007bff/ffffff?text=Photo" class="preview-img" alt="Photo">
                <div class="photo-label">
                    <label class="btn btn-outline-primary btn-sm">
                        Photo (pas obligé)
                        <input type="file" name="photo" accept="image/*" onchange="p(event)" style="display:none">
                    </label>
                </div>
            </div> -->

            <button type="submit" class="btn btn-primary w-100 mt-2">S'inscrire</button>

            <div class="text-center mt-2 footer-text">
                <small class="text-muted">
                    Déjà un compte ? <a href="<?= base_url() ?>/utilisateur/connexion">Se connecter</a>
                </small>
            </div>
        </form>

        <div class="text-center mt-2">
            <small class="text-muted">© 2025 Soutra+. Tous droits réservés.</small>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function p(e){if(e.target.files[0]){let r=new FileReader;r.onload=t=>document.getElementById('preview').src=t.target.result;r.readAsDataURL(e.target.files[0])}}
</script>
</body>
</html>