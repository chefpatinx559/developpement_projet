<?php
require "database/database.php";
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

        <section class="content">
            <div class="container-fluid">

                <!-- Message Flash -->
                <!-- <?php if ($message): ?>
                    <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show">
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?> -->

                <!-- FILTRE PAR CLIENT -->
                <div class="filter-card mb-4">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label fw-bold">Filtrer par client</label>
                            <select name="client" class="form-select">
                                <option value="">Tous les clients</option>
                                <?php foreach ($clients as $c): ?>
                                    <option value="<?= $c['code_client'] ?>" <?= ($client_filter === $c['code_client']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['nom_prenom_client']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                        </div>
                        <div class="col-md-3">
                            <a href="crud_documents.php" class="btn btn-secondary w-100">Réinitialiser</a>
                        </div>
                    </form>
                </div>

                <!-- CARD PRINCIPALE -->
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h3 class="card-title">
                            Liste des documents
                            <!-- <?php if ($client_filter): ?>
                                <small class="text-muted">
                                    — Client : <?= htmlspecialchars(array_column($clients, 'nom_prenom_client', 'code_client')[$client_filter] ?? '') ?>
                                </small>
                            <?php endif; ?> -->
                        </h3>

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
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="documentsTable">
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
                                    <!-- Ton code PHP de boucle reste identique -->
                                    <!-- <?php if ($documents): foreach ($documents as $d): ?>
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
                                                <span class="badge bg-<?= 
                                                    $d['etat_document'] === 'valide' ? 'success' : 
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
                                    <?php endforeach; else: ?> -->
                                        <tr><td colspan="8" class="text-center py-4 text-muted">Aucun document trouvé.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

  

<!-- MODAL (inchangé) -->
<!-- ... (ton modal reste exactement le même) ... -->

<!-- SCRIPTS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

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
</script>
</body>
</html>