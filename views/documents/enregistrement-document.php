<?php
<<<<<<< HEAD
////session_start();
require "database/database.php";

=======
<<<<<<< HEAD
//session_start();
require "database/database.php";

// ==================== FONCTION GÉNÉRATION CODE DOCUMENT ====================
function genererCodeDocument($pdo) {
    $count = $pdo->query("SELECT COUNT(*) FROM documents")->fetchColumn();
    return 'DOC' . ($count + 1);
}

>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
// ==================== SUPPRESSION ====================
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM documents WHERE code_document = ?");
        $stmt->execute([$_GET['delete']]);
        $_SESSION['message'] = "Document supprimé avec succès.";
    } catch (Exception $e) {
        $_SESSION['message'] = "Erreur : " . $e->getMessage();
    }
<<<<<<< HEAD
    // header("Location: crud_documents.php" . (isset($_GET['client']) ? "?client=" . urlencode($_GET['client']) : ""));
    // exit;
=======
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
}

// ==================== AJOUT / MODIFICATION ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $code_doc = trim($_POST['code_document']);
    $titre = trim($_POST['titre_document']);
    $numero = trim($_POST['numero_document']);
    $date_delivrance = $_POST['date_delivrance_document'];
    $date_expiration = $_POST['date_expiration_document'];
    $observation = $_POST['observation_document'];
    $etat = trim($_POST['etat_document']);
    $code_client = trim($_POST['code_client']);

<<<<<<< HEAD
=======
    // Génération automatique du code lors de l'ajout si vide
    if ($action === 'add' && empty($code_doc)) {
        $code_doc = genererCodeDocument($pdo);
    }

>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
    try {
        if ($action === 'add') {
            $sql = "INSERT INTO documents
                    (code_document, titre_document, numero_document, date_delivrance_document,
                     date_expiration_document, observation_document, etat_document, code_client)
                    VALUES (?,?,?,?,?,?,?,?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$code_doc, $titre, $numero, $date_delivrance, $date_expiration, $observation, $etat, $code_client]);
<<<<<<< HEAD
            $_SESSION['message'] = "Document ajouté avec succès.";
=======
            $_SESSION['message'] = "Document ajouté avec succès. Code généré : <strong>$code_doc</strong>";
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
        }
        if ($action === 'update') {
            $sql = "UPDATE documents SET
                    titre_document = ?, numero_document = ?, date_delivrance_document = ?,
                    date_expiration_document = ?, observation_document = ?, etat_document = ?, code_client = ?
                    WHERE code_document = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$titre, $numero, $date_delivrance, $date_expiration, $observation, $etat, $code_client, $code_doc]);
            $_SESSION['message'] = "Document modifié avec succès.";
        }
    } catch (Exception $e) {
        $_SESSION['message'] = "Erreur : " . $e->getMessage();
    }
    $redirect = "crud_documents.php";
    if (isset($_GET['client'])) $redirect .= "?client=" . urlencode($_GET['client']);
<<<<<<< HEAD
   // header("Location: $redirect");
    //exit;
=======
   
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
}

// ==================== FILTRE PAR CLIENT ====================
$client_filter = $_GET['client'] ?? '';
$where = $client_filter ? "WHERE d.code_client = ?" : "";
$params = $client_filter ? [$client_filter] : [];

// ==================== LISTE DOCUMENTS + CLIENTS ====================
$sql = "
    SELECT d.*, c.nom_prenom_client
    FROM documents d
    LEFT JOIN clients c ON d.code_client = c.code_client
    $where
    ORDER BY c.nom_prenom_client, d.date_delivrance_document DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$documents = $stmt->fetchAll();

// Liste des clients pour le select
$clients = $pdo->query("SELECT code_client, nom_prenom_client FROM clients ORDER BY nom_prenom_client")->fetchAll();

// ==================== MESSAGE FLASH ====================
$message = $_SESSION['message'] ?? '';
$alert_type = str_contains($message, 'Erreur') ? 'danger' : 'success';
if ($message) unset($_SESSION['message']);
<<<<<<< HEAD

// ==================== UTILISATEUR CONNECTÉ ====================
$user_name = "Jean Dupont";
$user_role = "Administrateur";
$user_photo = "https://via.placeholder.com/160x160/007bff/ffffff?text=JD";
=======
=======
require "database/database.php";
>>>>>>> 9ecb113a2e5352327ff75a3e20f37459a2a5e2b8
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Soutra+ | Documents par Client</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .user-panel img { width: 2.1rem; height: 2.1rem; object-fit: cover; }
        .nav-sidebar .nav-link { border-radius: 0.25rem; }
        .nav-treeview .nav-link { padding-left: 2.5rem; }
        .badge { font-size: 0.85em; }
        .expired { color: #dc3545; font-weight: 600; }
        .expiring { color: #ffc107; font-weight: 600; }
        .filter-card { background: #f8f9fa; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem; }
<<<<<<< HEAD
=======

        /* Style bouton Excel vert foncé */
        .btn-excel {
            background-color: #1D6F42;
            border-color: #1D6F42;
            color: white;
        }
        .btn-excel:hover {
            background-color: #165a34;
            border-color: #165a34;
            color: white;
        }
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php include 'config/dashboard.php'; ?>
<<<<<<< HEAD

    <!-- ==================== CONTENT ==================== -->
=======
<<<<<<< HEAD
=======

>>>>>>> 9ecb113a2e5352327ff75a3e20f37459a2a5e2b8
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Documents par Client</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Accueil</a></li>
                            <li class="breadcrumb-item active">Documents</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
<<<<<<< HEAD

        <section class="content">
            <div class="container-fluid">

                <!-- Message Flash -->
                <?php if ($message): ?>
                    <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show">
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- === FILTRE PAR CLIENT === -->
                <div class="filter-card">
=======
        <section class="content">
            <div class="container-fluid">
                <!-- Message Flash -->
                <!-- <?php if ($message): ?>
                    <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show">
                        <?= $message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?> -->

                <!-- FILTRE PAR CLIENT -->
<<<<<<< HEAD
                <div class="filter-card">
=======
                <div class="filter-card mb-4">
>>>>>>> 9ecb113a2e5352327ff75a3e20f37459a2a5e2b8
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label fw-bold">Filtrer par client</label>
                            <select name="client" class="form-select">
                                <option value="">Tous les clients</option>
                                <?php foreach ($clients as $c): ?>
<<<<<<< HEAD
                                    <option value="<?= $c['code_client'] ?>" 
                                        <?= ($client_filter === $c['code_client']) ? 'selected' : '' ?>>
=======
<<<<<<< HEAD
                                    <option value="<?= $c['code_client'] ?>"
                                        <?= ($client_filter === $c['code_client']) ? 'selected' : '' ?>>
=======
                                    <option value="<?= $c['code_client'] ?>" <?= ($client_filter === $c['code_client']) ? 'selected' : '' ?>>
>>>>>>> 9ecb113a2e5352327ff75a3e20f37459a2a5e2b8
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                                        <?= htmlspecialchars($c['nom_prenom_client']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
<<<<<<< HEAD
                            <button type="submit" class="btn btn-primary w-100">
                                Filtrer
                            </button>
                        </div>
                        <div class="col-md-3">
                            <a href="crud_documents.php" class="btn btn-secondary w-100">
                                Réinitialiser
                            </a>
=======
                            <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                        </div>
                        <div class="col-md-3">
<<<<<<< HEAD
                            <?php if ($client_filter): ?>
                                <a href="crud_documents.php" class="btn btn-secondary w-100">Réinitialiser</a>
                            <?php else: ?>
                                <button type="button" class="btn btn-secondary w-100" disabled>Réinitialiser</button>
                            <?php endif; ?>
=======
                            <a href="crud_documents.php" class="btn btn-secondary w-100">Réinitialiser</a>
>>>>>>> 9ecb113a2e5352327ff75a3e20f37459a2a5e2b8
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                        </div>
                    </form>
                </div>

<<<<<<< HEAD
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">
                            Liste des documents 
                            <?php if ($client_filter): ?>
                                <small class="text-muted">
                                    — Client : 
                                    <?= htmlspecialchars(array_column($clients, 'nom_prenom_client', 'code_client')[$client_filter] ?? '') ?>
                                </small>
                            <?php endif; ?>
                        </h3>
                        <button class="btn btn-success" id="addBtn">
                            Ajouter un document
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
=======
                <!-- CARD PRINCIPALE -->
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h3 class="card-title">
                            Liste des documents
<<<<<<< HEAD
                            <?php if ($client_filter): ?>
                                <small class="text-muted">
                                    — Client :
                                    <?= htmlspecialchars(array_column($clients, 'nom_prenom_client', 'code_client')[$client_filter] ?? '') ?>
=======
                            <!-- <?php if ($client_filter): ?>
                                <small class="text-muted">
                                    — Client : <?= htmlspecialchars(array_column($clients, 'nom_prenom_client', 'code_client')[$client_filter] ?? '') ?>
>>>>>>> 9ecb113a2e5352327ff75a3e20f37459a2a5e2b8
                                </small>
                            <?php endif; ?> -->
                        </h3>
<<<<<<< HEAD
                        <button class="btn btn-success" id="addBtn">Ajouter un document</button>
=======

                        <!-- BOUTONS EN HAUT À DROITE -->
                        <div class="card-tools">
                            <button type="button" class="btn btn-success btn-sm me-2" onclick="exportTableToCSV()">
                                Exporter CSV
                            </button>
                            <button type="button" class="btn btn-excel btn-sm me-2" onclick="exportTableToExcel()">
                                Exporter Excel
                            </button>
                            <button name="ajouter" class="btn btn-primary btn-sm" id="addBtn">
                                Ajouter un document
                            </button>
                        </div>
>>>>>>> 9ecb113a2e5352327ff75a3e20f37459a2a5e2b8
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="documentsTable">
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                                <thead class="table-dark">
                                    <tr>
                                        <th>Code</th>
                                        <th>Titre</th>
                                        <th>Numéro</th>
                                        <th>Client</th>
                                        <th>Délivrance</th>
                                        <th>Expiration</th>
                                        <th>État</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
<<<<<<< HEAD
                                    <?php if ($documents): foreach ($documents as $d): ?>
=======
                                    <!-- Ton code PHP de boucle reste identique -->
                                    <!-- <?php if ($documents): foreach ($documents as $d): ?>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                                        <?php
                                        $today = new DateTime();
                                        $exp = new DateTime($d['date_expiration_document']);
                                        $days_left = $today->diff($exp)->days;
                                        $is_expired = $exp < $today;
                                        $is_expiring = !$is_expired && $days_left <= 30;
                                        ?>
                                        <tr>
                                            <td><code><?= htmlspecialchars($d['code_document']) ?></code></td>
                                            <td><?= htmlspecialchars($d['titre_document']) ?></td>
                                            <td><?= htmlspecialchars($d['numero_document']) ?></td>
                                            <td><strong><?= htmlspecialchars($d['nom_prenom_client'] ?? '—') ?></strong></td>
                                            <td><?= date('d/m/Y', strtotime($d['date_delivrance_document'])) ?></td>
                                            <td class="<?= $is_expired ? 'expired' : ($is_expiring ? 'expiring' : '') ?>">
                                                <?= date('d/m/Y', strtotime($d['date_expiration_document'])) ?>
                                                <?php if ($is_expiring && !$is_expired): ?>
                                                    <small>(<?= $days_left ?> j)</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
<<<<<<< HEAD
                                                <span class="badge bg-<?= 
                                                    $d['etat_document'] === 'valide' ? 'success' : 
=======
                                                <span class="badge bg-<?=
                                                    $d['etat_document'] === 'valide' ? 'success' :
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                                                    ($d['etat_document'] === 'expiré' ? 'danger' : 'warning')
                                                ?>">
                                                    <?= ucfirst($d['etat_document']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-warning btn-sm edit-btn"
                                                    data-bs-code="<?= htmlspecialchars($d['code_document']) ?>"
                                                    data-bs-titre="<?= htmlspecialchars($d['titre_document']) ?>"
                                                    data-bs-numero="<?= htmlspecialchars($d['numero_document']) ?>"
                                                    data-bs-delivrance="<?= $d['date_delivrance_document'] ?>"
                                                    data-bs-expiration="<?= $d['date_expiration_document'] ?>"
                                                    data-bs-obs="<?= htmlspecialchars($d['observation_document']) ?>"
                                                    data-bs-etat="<?= htmlspecialchars($d['etat_document']) ?>"
                                                    data-bs-client="<?= htmlspecialchars($d['code_client']) ?>">
                                                    Modifier
                                                </button>
                                                <a href="?delete=<?= urlencode($d['code_document']) ?>&client=<?= urlencode($client_filter) ?>"
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Supprimer ce document ?');">
                                                    Supprimer
                                                </a>
                                            </td>
                                        </tr>
<<<<<<< HEAD
                                    <?php endforeach; else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                Aucun document trouvé.
                                                <?php if ($client_filter): ?>
                                                    <br><small>Cliquez sur "Réinitialiser" pour voir tous les clients.</small>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
=======
                                    <?php endforeach; else: ?> -->
                                        <tr><td colspan="8" class="text-center py-4 text-muted">Aucun document trouvé.</td></tr>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
<<<<<<< HEAD

    <footer class="main-footer">
        <strong>© 2025 <a href="#">Soutra+</a>.</strong> Tous droits réservés.
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0
        </div>
=======
<<<<<<< HEAD
    <footer class="main-footer">
        <strong>© 2025 <a href="#">Soutra+</a>.</strong> Tous droits réservés.
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
    </footer>
</div>

<!-- ==================== MODAL DOCUMENT ==================== -->
<div class="modal fade" id="docModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitle">Ajouter un document</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="docForm" method="post">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <div class="row g-3">
                        <div class="col-md-6">
<<<<<<< HEAD
                            <label class="form-label">Code document <span class="text-danger">*</span></label>
                            <input type="text" name="code_document" id="code_document" class="form-control" required>
=======
                            <label class="form-label">Code document <span class="text-muted">(auto-généré)</span></label>
                            <input type="text" name="code_document" id="code_document" class="form-control" readonly placeholder="Ex: DOC46">
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Titre document <span class="text-danger">*</span></label>
                            <input type="text" name="titre_document" id="titre_document" class="form-control" placeholder="Ex: Passeport, CNI" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Numéro document <span class="text-danger">*</span></label>
                            <input type="text" name="numero_document" id="numero_document" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Client <span class="text-danger">*</span></label>
<<<<<<< HEAD
                            <select name="code_client" id="code_client" class="form-select" >
=======
                            <select name="code_client" id="code_client" class="form-select" required>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                                <option value="">-- Sélectionner un client --</option>
                                <?php foreach ($clients as $c): ?>
                                    <option value="<?= $c['code_client'] ?>"><?= htmlspecialchars($c['nom_prenom_client']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date délivrance <span class="text-danger">*</span></label>
                            <input type="date" name="date_delivrance_document" id="date_delivrance_document" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date expiration <span class="text-danger">*</span></label>
                            <input type="date" name="date_expiration_document" id="date_expiration_document" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">État <span class="text-danger">*</span></label>
                            <select name="etat_document" id="etat_document" class="form-select" required>
                                <option value="valide">Valide</option>
                                <option value="expiré">Expiré</option>
                                <option value="en attente">En attente</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Observation</label>
                            <textarea name="observation_document" id="observation_document" class="form-control" rows="3" placeholder="Remarques..."></textarea>
                        </div>
                    </div>
                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-success px-4">Sauvegarder</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<<<<<<< HEAD
<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
    const modal = new bootstrap.Modal('#docModal');
    const form = document.getElementById('docForm');

    // Ajouter
=======
=======

  

<!-- MODAL (inchangé) -->
<!-- ... (ton modal reste exactement le même) ... -->

<!-- SCRIPTS -->
>>>>>>> 9ecb113a2e5352327ff75a3e20f37459a2a5e2b8
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<<<<<<< HEAD
    // === AJOUTER UN DOCUMENT → CODE AUTO ===
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
    document.getElementById('addBtn').addEventListener('click', () => {
        form.reset();
        document.getElementById('modalTitle').innerText = 'Ajouter un document';
        document.getElementById('formAction').value = 'add';
<<<<<<< HEAD
        document.getElementById('code_document').readOnly = false;
        modal.show();
    });

    // Modifier
=======
        document.getElementById('code_document').readOnly = true;
        document.getElementById('code_document').value = '<?= genererCodeDocument($pdo) ?>';
        modal.show();
    });

    // === MODIFIER UN DOCUMENT ===
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('modalTitle').innerText = 'Modifier un document';
            document.getElementById('formAction').value = 'update';
            document.getElementById('code_document').value = this.dataset.bsCode;
            document.getElementById('code_document').readOnly = true;
            document.getElementById('titre_document').value = this.dataset.bsTitre;
            document.getElementById('numero_document').value = this.dataset.bsNumero;
            document.getElementById('date_delivrance_document').value = this.dataset.bsDelivrance;
            document.getElementById('date_expiration_document').value = this.dataset.bsExpiration;
            document.getElementById('observation_document').value = this.dataset.bsObs;
            document.getElementById('etat_document').value = this.dataset.bsEtat;
            document.getElementById('code_client').value = this.dataset.bsClient;
            modal.show();
        });
    });
<<<<<<< HEAD
=======
=======
<script>
// === FONCTIONS D'EXPORT (100% sans librairie) ===
function exportTableToCSV() {
    const table = document.querySelector("#documentsTable");
    const rows = table.querySelectorAll("tr");
    let csv = [];

    for (let i = 0; i < rows.length; i++) {
        const row = [], cols = rows[i].querySelectorAll("td, th");
        for (let j = 0; j < cols.length - 1; j++) { // -1 = ignore colonne Actions
            let data = cols[j].innerText.trim().replace(/"/g, '""');
            row.push('"' + data + '"');
        }
        if (row.length > 0) csv.push(row.join(";"));
    }

    const csvContent = "\uFEFF" + csv.join("\n");
    const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = "documents_" + new Date().toLocaleDateString("fr-FR").replace(/\//g, "-") + ".csv";
    link.click();
}

function exportTableToExcel() {
    const table = document.querySelector("#documentsTable");
    const rows = table.querySelectorAll("tr");

    let html = `<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
    <head><meta charset="utf-8"><style>td { mso-number-format:"\@"; }</style></head><body><table border="1">`;

    for (let i = 0; i < rows.length; i++) {
        const cols = rows[i].querySelectorAll("td, th");
        html += "<tr>";
        for (let j = 0; j < cols.length - 1; j++) {
            let text = cols[j].innerText.trim().replace(/"/g, '""');
            html += `<td>${text}</td>`;
        }
        html += "</tr>";
    }
    html += `</table></body></html>`;

    const blob = new Blob(['\ufeff', html], { type: 'application/vnd.ms-excel' });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = "documents_" + new Date().toLocaleDateString("fr-FR").replace(/\//g, "-") + ".xls";
    link.click();
}

// === MODAL (inchangé) ===
const modal = new bootstrap.Modal('#docModal');
const form = document.getElementById('docForm');

document.getElementById('addBtn').addEventListener('click', () => {
    form.reset();
    document.getElementById('modalTitle').innerText = 'Ajouter un document';
    document.getElementById('formAction').value = 'add';
    document.getElementById('code_document').readOnly = false;
    modal.show();
});

document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('modalTitle').innerText = 'Modifier un document';
        document.getElementById('formAction').value = 'update';
        document.getElementById('code_document').value = this.dataset.bsCode;
        document.getElementById('code_document').readOnly = true;
        document.getElementById('titre_document').value = this.dataset.bsTitre;
        document.getElementById('numero_document').value = this.dataset.bsNumero;
        document.getElementById('date_delivrance_document').value = this.dataset.bsDelivrance;
        document.getElementById('date_expiration_document').value = this.dataset.bsExpiration;
        document.getElementById('observation_document').value = this.dataset.bsObs;
        document.getElementById('etat_document').value = this.dataset.bsEtat;
        document.getElementById('code_client').value = this.dataset.bsClient;
        modal.show();
    });
});
>>>>>>> 9ecb113a2e5352327ff75a3e20f37459a2a5e2b8
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
</script>
</body>
</html>