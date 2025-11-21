<?php
// session_start();
require "database/database.php";

// ==================== FILTRE PAR MATRICULE ====================
$matricule_filter = $_GET['matricule'] ?? '';
$where = $matricule_filter ? "WHERE a.matricule = ?" : "";
$params = $matricule_filter ? [$matricule_filter] : [];

// ==================== SUPPRESSION ====================
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM albums WHERE code_album = ?");
        $stmt->execute([$_GET['delete']]);
        $_SESSION['message'] = "Album supprimé avec succès.";
    } catch (Exception $e) {
        $_SESSION['message'] = "Erreur lors de la suppression : " . $e->getMessage();
    }
    // Redirection pour nettoyer l'URL après suppression
    $redirect = http_build_query(array_filter(['matricule' => $matricule_filter]));
    
}

// ==================== AJOUT / MODIFICATION ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $code = trim($_POST['code_album']);
    $titre = trim($_POST['titre_album']);
    $matricule = trim($_POST['matricule']);
    $etat = trim($_POST['etat_album']);
    $photo = null;
    $type_photo = '';

    if (isset($_FILES['photo_album']) && $_FILES['photo_album']['error'] === UPLOAD_ERR_OK) {
        $photo = file_get_contents($_FILES['photo_album']['tmp_name']);
        $type_photo = $_FILES['photo_album']['type'];
    }

    try {
        if ($action === 'add') {
            $sql = "INSERT INTO albums
                    (code_album, titre_album, photo_album, type_photo, matricule, etat_album)
                    VALUES (?,?,?,?,?,?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$code, $titre, $photo, $type_photo, $matricule, $etat]);
            $_SESSION['message'] = "Album ajouté avec succès.";
        }
        if ($action === 'update') {
            if ($photo) {
                $sql = "UPDATE albums SET
                        titre_album = ?, photo_album = ?, type_photo = ?, matricule = ?, etat_album = ?
                        WHERE code_album = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$titre, $photo, $type_photo, $matricule, $etat, $code]);
            } else {
                $sql = "UPDATE albums SET
                        titre_album = ?, matricule = ?, etat_album = ?
                        WHERE code_album = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$titre, $matricule, $etat, $code]);
            }
            $_SESSION['message'] = "Album modifié avec succès.";
        }
    } catch (Exception $e) {
        $_SESSION['message'] = "Erreur : " . $e->getMessage();
    }
    // Redirection après POST pour éviter re-soumission
    $redirect = http_build_query(array_filter(['matricule' => $matricule_filter]));
   
}

// ==================== LISTE ALBUMS + FILTRE ====================
$stmt = $pdo->prepare("
    SELECT a.*, u.nom_prenom
    FROM albums a
    LEFT JOIN utilisateurs u ON a.matricule = u.utilisateur_id
    $where
    ORDER BY a.titre_album
");
$stmt->execute($params);
$albums = $stmt->fetchAll();

$utilisateurs = $pdo->query("SELECT utilisateur_id, nom_prenom FROM utilisateurs ORDER BY nom_prenom")->fetchAll();

// ==================== MESSAGE FLASH ====================
$message = $_SESSION['message'] ?? '';
$alert_type = str_starts_with($message, 'Erreur') ? 'danger' : 'success';
if ($message) unset($_SESSION['message']);

// ==================== UTILISATEUR CONNECTÉ ====================
$user_name = "Jean Dupont";
$user_role = "Administrateur";
$user_photo = "https://via.placeholder.com/160x160/007bff/ffffff?text=JD";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Soutra+ | Gestion des Albums</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .user-panel img { width: 2.1rem; height: 2.1rem; object-fit: cover; }
        .nav-sidebar .nav-link { border-radius: 0.25rem; }
        .nav-treeview .nav-link { padding-left: 2.5rem; }
        .badge { font-size: 0.85em; }
        .album-img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; }
        .album-card-img { width: 100%; height: 180px; object-fit: cover; border-radius: 10px 10px 0 0; }
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
                        <h1>Gestion des Albums</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Accueil</a></li>
                            <li class="breadcrumb-item active">Albums</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <?php if ($message): ?>
                    <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show">
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">
                            Liste des albums
                            <?php if ($matricule_filter): ?>
                                <span class="badge bg-info ms-2">
                                    Filtré par : <?= htmlspecialchars($utilisateurs[array_search($matricule_filter, array_column($utilisateurs, 'utilisateur_id'))]['nom_prenom'] ?? $matricule_filter) ?>
                                </span>
                            <?php endif; ?>
                        </h3>
                        <div class="d-flex gap-2">
                            <!-- FILTRE PAR MATRICULE -->
                            <form method="get" class="d-flex gap-2">
                                <select name="matricule" class="form-select" style="width: auto;">
                                    <option value="">Tous les utilisateurs</option>
                                    <?php foreach ($utilisateurs as $u): ?>
                                        <option value="<?= $u['utilisateur_id'] ?>" <?= $matricule_filter == $u['utilisateur_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($u['nom_prenom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-outline-primary">Filtrer</button>
                            </form>
                            <button class="btn btn-success" id="addBtn">Ajouter un album</button>
                        </div>
                    </div>

                    <div class="card-body">
                        <?php if (empty($albums)): ?>
                            <div class="alert alert-info">
                                <?= $matricule_filter ? "Aucun album trouvé pour cet utilisateur." : "Aucun album enregistré." ?>
                            </div>
                        <?php elseif ($matricule_filter): ?>
                            <!-- AFFICHAGE EN GRILLE SI FILTRE ACTIF -->
                            <div class="row">
                                <?php foreach ($albums as $a): ?>
                                    <div class="col-md-4 mb-4">
                                        <div class="card h-100 shadow-sm">
                                            <?php if ($a['photo_album']): ?>
                                                <img src="data:<?= htmlspecialchars($a['type_photo']) ?>;base64,<?= base64_encode($a['photo_album']) ?>"
                                                     class="album-card-img" alt="<?= htmlspecialchars($a['titre_album']) ?>">
                                            <?php else: ?>
                                                <div class="bg-light border album-card-img d-flex align-items-center justify-content-center">
                                                    <span class="text-muted">Pas d'image</span>
                                                </div>
                                            <?php endif; ?>
                                            <div class="card-body">
                                                <h5 class="card-title"><?= htmlspecialchars($a['titre_album']) ?></h5>
                                                <p class="card-text small">
                                                    <strong>Code :</strong> <?= htmlspecialchars($a['code_album']) ?><br>
                                                    <strong>Propriétaire :</strong> <?= htmlspecialchars($a['nom_prenom'] ?? '—') ?><br>
                                                    <strong>État :</strong>
                                                    <span class="badge bg-<?= $a['etat_album'] === 'actif' ? 'success' : 'secondary' ?>">
                                                        <?= ucfirst($a['etat_album']) ?>
                                                    </span>
                                                </p>
                                                <div class="d-flex gap-1">
                                                    <button class="btn btn-warning btn-sm edit-btn flex-fill"
                                                            data-bs-code="<?= htmlspecialchars($a['code_album']) ?>"
                                                            data-bs-titre="<?= htmlspecialchars($a['titre_album']) ?>"
                                                            data-bs-matricule="<?= htmlspecialchars($a['matricule']) ?>"
                                                            data-bs-etat="<?= htmlspecialchars($a['etat_album']) ?>">
                                                        Modifier
                                                    </button>
                                                    <?php 
                                                    $delete_url = '?' . http_build_query(array_filter([
                                                        'delete' => $a['code_album'],
                                                        'matricule' => $matricule_filter
                                                    ]));
                                                    ?>
                                                    <a href="<?= $delete_url ?>" 
                                                       class="btn btn-danger btn-sm flex-fill"
                                                       onclick="return confirm('Supprimer définitivement cet album ?');">
                                                        Supprimer
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <!-- AFFICHAGE EN TABLEAU SI PAS DE FILTRE -->
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Code</th>
                                            <th>Titre</th>
                                            <th>Photo</th>
                                            <th>Matricule</th>
                                            <th>Propriétaire</th>
                                            <th>État</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($albums as $a): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($a['code_album']) ?></strong></td>
                                                <td><?= htmlspecialchars($a['titre_album']) ?></td>
                                                <td>
                                                    <?php if ($a['photo_album']): ?>
                                                        <img src="data:<?= htmlspecialchars($a['type_photo']) ?>;base64,<?= base64_encode($a['photo_album']) ?>"
                                                             class="album-img" alt="Photo">
                                                    <?php else: ?>
                                                        <span class="text-muted">Aucune</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($a['matricule']) ?></td>
                                                <td><?= htmlspecialchars($a['nom_prenom'] ?? '—') ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $a['etat_album'] === 'actif' ? 'success' : 'secondary' ?>">
                                                        <?= ucfirst($a['etat_album']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-warning btn-sm edit-btn"
                                                            data-bs-code="<?= htmlspecialchars($a['code_album']) ?>"
                                                            data-bs-titre="<?= htmlspecialchars($a['titre_album']) ?>"
                                                            data-bs-matricule="<?= htmlspecialchars($a['matricule']) ?>"
                                                            data-bs-etat="<?= htmlspecialchars($a['etat_album']) ?>">
                                                        Modifier
                                                    </button>
                                                    <?php 
                                                    $delete_url = '?' . http_build_query(array_filter([
                                                        'delete' => $a['code_album'],
                                                        'matricule' => $matricule_filter ?: null
                                                    ]));
                                                    ?>
                                                    <a href="<?= $delete_url ?>" 
                                                       class="btn btn-danger btn-sm"
                                                       onclick="return confirm('Supprimer définitivement cet album ?');">
                                                        Supprimer
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
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

<!-- ==================== MODAL ALBUM ==================== -->
<div class="modal fade" id="albumModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Ajouter un album</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="albumForm" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Code album <span class="text-danger">*</span></label>
                            <input type="text" name="code_album" id="code_album" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Titre album <span class="text-danger">*</span></label>
                            <input type="text" name="titre_album" id="titre_album" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Matricule utilisateur <span class="text-danger">*</span></label>
                            <select name="matricule" id="matricule" class="form-control" required>
                                <option value="">-- Sélectionner --</option>
                                <?php foreach ($utilisateurs as $u): ?>
                                    <option value="<?= $u['utilisateur_id'] ?>">
                                        <?= htmlspecialchars($u['nom_prenom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">État</label>
                            <select name="etat_album" id="etat_album" class="form-control">
                                <option value="actif">Actif</option>
                                <option value="inactif">Inactif</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Photo album</label>
                            <input type="file" name="photo_album" id="photo_album" class="form-control" accept="image/*">
                            <small class="text-muted">Laissez vide pour conserver l'image actuelle (en modification)</small>
                        </div>
                    </div>
                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-success">Sauvegarder</button>
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
    const modal = new bootstrap.Modal('#albumModal');
    const form = document.getElementById('albumForm');

    document.getElementById('addBtn').addEventListener('click', () => {
        form.reset();
        document.getElementById('modalTitle').innerText = 'Ajouter un album';
        document.getElementById('formAction').value = 'add';
        document.getElementById('code_album').readOnly = false;
        modal.show();
    });

    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('modalTitle').innerText = 'Modifier un album';
            document.getElementById('formAction').value = 'update';
            document.getElementById('code_album').value = this.dataset.bsCode;
            document.getElementById('code_album').readOnly = true;
            document.getElementById('titre_album').value = this.dataset.bsTitre;
            document.getElementById('matricule').value = this.dataset.bsMatricule;
            document.getElementById('etat_album').value = this.dataset.bsEtat;
            modal.show();
        });
    });
</script>
</body>
</html>