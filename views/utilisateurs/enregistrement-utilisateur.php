<?php
// DÉMARRAGE DE LA SESSION
//session_start();
require "database/database.php";

// ==================== VARIABLES DASHBOARD ====================
$user_name = $_SESSION['nom_prenom'] ?? "Utilisateur Anonyme";
$user_role = $_SESSION['role'] ?? "Réception";
// Photo utilisateur sécurisée
if (!empty($_SESSION['photo']) && !empty($_SESSION['type_photo'])) {
    $user_photo = 'data:' . $_SESSION['type_photo'] . ';base64,' . base64_encode($_SESSION['photo']);
} else {
    $initials = mb_substr($user_name, 0, 2);
    $user_photo = "https://via.placeholder.com/160x160/6c757d/ffffff?text=" . urlencode($initials);
}

// ==================== SUPPRESSION ====================
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE utilisateur_id = ?");
        $stmt->execute([$_GET['delete']]);
        $_SESSION['message'] = "Utilisateur supprimé avec succès.";
    } catch (Exception $e) {
        $_SESSION['message'] = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

// ==================== AJOUT / MODIFICATION ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = trim($_POST['utilisateur_id']);
    $nom = trim($_POST['nom_prenom']);
    $login = trim($_POST['login']);
    $mdp = $_POST['mdp'] ?? '';               // Toujours pris en compte
    $telephone = trim($_POST['telephone']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $etat = $_POST['etat'] ?? 'actif';

    // Gestion photo
    $photo = null;
    $type_photo = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $allowed = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['photo']['type'], $allowed) && $_FILES['photo']['size'] <= 2 * 1024 * 1024) {
            $photo = file_get_contents($_FILES['photo']['tmp_name']);
            $type_photo = $_FILES['photo']['type'];
        }
    }

    try {
        if ($action === 'add') {
            $check = $pdo->prepare("SELECT utilisateur_id FROM utilisateurs WHERE login = ?");
            $check->execute([$login]);
            if ($check->fetch()) {
                $_SESSION['message'] = "Erreur : Ce login existe déjà.";
            } else {
                $hash = password_hash($mdp, PASSWORD_BCRYPT);
                $sql = "INSERT INTO utilisateurs
                        (utilisateur_id, nom_prenom, login, mdp, telephone, email, role, photo, type_photo, etat)
                        VALUES (?,?,?,?,?,?,?,?,?,?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id, $nom, $login, $hash, $telephone, $email, $role, $photo, $type_photo, $etat]);
                $_SESSION['message'] = "Utilisateur ajouté avec succès.";
            }
        }
        if ($action === 'update') {
            $sql = "UPDATE utilisateurs SET
                    nom_prenom = ?, login = ?, telephone = ?, email = ?, role = ?, etat = ?";
            $params = [$nom, $login, $telephone, $email, $role, $etat];

            // Le mot de passe est TOUJOURS mis à jour s’il est rempli
            if (!empty($mdp)) {
                $sql .= ", mdp = ?";
                $params[] = password_hash($mdp, PASSWORD_BCRYPT);
            }
            if ($photo !== null) {
                $sql .= ", photo = ?, type_photo = ?";
                $params[] = $photo;
                $params[] = $type_photo;
            }
            $sql .= " WHERE utilisateur_id = ?";
            $params[] = $id;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $_SESSION['message'] = "Utilisateur modifié avec succès.";
        }
    } catch (Exception $e) {
        $_SESSION['message'] = "Erreur : " . $e->getMessage();
    }
}

// ==================== LISTE TOUS LES UTILISATEURS ====================
$stmt = $pdo->query("
    SELECT utilisateur_id, nom_prenom, login, telephone, email, role, etat, photo, type_photo
    FROM utilisateurs
    ORDER BY nom_prenom
");
$utilisateurs = $stmt->fetchAll();

// ==================== MESSAGE FLASH ====================
$message = $_SESSION['message'] ?? '';
$alert_type = (strpos($message, 'Erreur') === 0) ? 'danger' : 'success';
if ($message) unset($_SESSION['message']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Soutra+ | Gestion des Utilisateurs</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .user-panel img { width: 2.1rem; height: 2.1rem; object-fit: cover; border-radius: 50%; }
        .nav-sidebar .nav-link { border-radius: 0.25rem; }
        .nav-treeview .nav-link { padding-left: 2.5rem; }
        .badge { font-size: 0.85em; }
        .user-photo { width: 40px; height: 40px; object-fit: cover; border-radius: 50%; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php include 'config/dashboard.php'; ?>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Gestion des Utilisateurs</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Accueil</a></li>
                            <li class="breadcrumb-item active">Utilisateurs</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <!-- MESSAGE FLASH -->
                <?php if ($message): ?>
                    <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show">
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Liste des utilisateurs</h3>
                        <button class="btn btn-primary" id="addBtn">
                            Ajouter un utilisateur
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Photo</th>
                                        <th>Nom</th>
                                        <th>Login</th>
                                        <th>Email</th>
                                        <th>Téléphone</th>
                                        <th>Rôle</th>
                                        <th>État</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($utilisateurs as $u): ?>
                                        <tr>
                                            <td>
                                                <?php if ($u['photo']): ?>
                                                    <img src="data:<?= htmlspecialchars($u['type_photo']) ?>;base64,<?= base64_encode($u['photo']) ?>"
                                                         class="user-photo" alt="Photo">
                                                <?php else: ?>
                                                    <img src="https://via.placeholder.com/40/6c757d/ffffff?text=<?= substr(htmlspecialchars($u['nom_prenom']), 0, 2) ?>"
                                                         class="user-photo" alt="Photo">
                                                <?php endif; ?>
                                            </td>
                                            <td><strong><?= htmlspecialchars($u['nom_prenom']) ?></strong></td>
                                            <td><?= htmlspecialchars($u['login']) ?></td>
                                            <td><?= htmlspecialchars($u['email'] ?? '—') ?></td>
                                            <td><?= htmlspecialchars($u['telephone'] ?? '—') ?></td>
                                            <td>
                                                <span class="badge bg-<?=
                                                    $u['role'] === 'Administrateur' ? 'danger' :
                                                    ($u['role'] === 'Superviseur' ? 'warning' :
                                                    ($u['role'] === 'Comptable' ? 'info' : 'primary')) ?>">
                                                    <?= ucfirst($u['role']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $u['etat'] === 'actif' ? 'success' : 'secondary' ?>">
                                                    <?= ucfirst($u['etat']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-warning btn-sm edit-btn"
                                                    data-bs-id="<?= htmlspecialchars($u['utilisateur_id']) ?>"
                                                    data-bs-nom="<?= htmlspecialchars($u['nom_prenom']) ?>"
                                                    data-bs-login="<?= htmlspecialchars($u['login']) ?>"
                                                    data-bs-email="<?= htmlspecialchars($u['email'] ?? '') ?>"
                                                    data-bs-tel="<?= htmlspecialchars($u['telephone'] ?? '') ?>"
                                                    data-bs-role="<?= htmlspecialchars($u['role']) ?>"
                                                    data-bs-etat="<?= htmlspecialchars($u['etat']) ?>">
                                                    Modifier
                                                </button>
                                                <a href="?delete=<?= urlencode($u['utilisateur_id']) ?>"
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Supprimer cet utilisateur ?');">
                                                    Supprimer
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <footer class="main-footer">
        <strong>© 2025 <a href="#">Soutra+</a>.</strong> Tous droits réservés.
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0
        </div>
    </footer>
</div>

<!-- MODAL -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Ajouter un utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="userForm" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">ID utilisateur <span class="text-danger">*</span></label>
                            <input type="text" name="utilisateur_id" id="utilisateur_id" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nom complet <span class="text-danger">*</span></label>
                            <input type="text" name="nom_prenom" id="nom_prenom" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Login <span class="text-danger">*</span></label>
                            <input type="text" name="login" id="login" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mot de passe <span class="text-danger">*</span></label>
                            <input type="password" name="mdp" id="mdp" class="form-control" minlength="6" required>
                            <small class="text-muted">Minimum 6 caractères – modifiable à tout moment</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Téléphone</label>
                            <input type="tel" name="telephone" id="telephone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Rôle <span class="text-danger">*</span></label>
                            <select name="role" id="role" class="form-control" required>
                                <option value="Réception">Réception</option>
                                <option value="Comptable">Comptable</option>
                                <option value="Superviseur">Superviseur</option>
                                <option value="Administrateur">Administrateur</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">État</label>
                            <select name="etat" id="etat" class="form-control">
                                <option value="actif">Actif</option>
                                <option value="inactif">Inactif</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Photo (max 2 Mo)</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                            <small class="text-muted">JPEG, PNG, GIF</small>
                        </div>
                    </div>
                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-success">
                            Sauvegarder
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
    const modal = new bootstrap.Modal('#userModal');

    // AJOUT
    document.getElementById('addBtn').addEventListener('click', () => {
        document.getElementById('userForm').reset();
        document.getElementById('modalTitle').innerText = 'Ajouter un utilisateur';
        document.getElementById('formAction').value = 'add';
        document.getElementById('utilisateur_id').readOnly = false;
        document.getElementById('mdp').required = true;
        document.getElementById('mdp').placeholder = "Mot de passe requis";
        modal.show();
    });

    // MODIFICATION
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('modalTitle').innerText = 'Modifier un utilisateur';
            document.getElementById('formAction').value = 'update';
            document.getElementById('utilisateur_id').value = this.dataset.bsId;
            document.getElementById('utilisateur_id').readOnly = true;
            document.getElementById('nom_prenom').value = this.dataset.bsNom;
            document.getElementById('login').value = this.dataset.bsLogin;
            document.getElementById('email').value = this.dataset.bsEmail;
            document.getElementById('telephone').value = this.dataset.bsTel;
            document.getElementById('role').value = this.dataset.bsRole;
            document.getElementById('etat').value = this.dataset.bsEtat;

            // Le champ mot de passe reste toujours modifiable
            document.getElementById('mdp').value = '';
            document.getElementById('mdp').required = false;   // pas obligatoire si on ne veut pas changer
            document.getElementById('mdp').placeholder = "Nouveau mot de passe (laisser vide pour conserver l’ancien)";
            modal.show();
        });
    });
</script>
</body>
</html>