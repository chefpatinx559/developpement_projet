<?php
// session_start();
require_once "database/database.php";

// Fonction commune
function formatMoney($amount) {
    return number_format((float)$amount, 0, ',', ' ') . ' FCFA';
}

// Récupération du mode de filtrage
$mode = $_GET['mode'] ?? 'facture'; // facture, client, type

// Filtres communs
$facture = $_GET['facture'] ?? '';
$client = $_GET['client'] ?? '';
$type = $_GET['type'] ?? '';
$debut = $_GET['debut'] ?? '';
$fin = $_GET['fin'] ?? '';

// Construction sécurisée de la requête
$where = "WHERE 1=1";
$params = [];

if ($mode === 'facture' && !empty($facture)) {
    $where .= " AND t.code_facture = ?";
    $params[] = $facture;
}
if ($mode === 'client' && !empty($client)) {
    $where .= " AND t.destinataire = ?";
    $params[] = $client;
}
if ($mode === 'type' && !empty($type)) {
    $where .= " AND t.type_transaction = ?";
    $params[] = $type;
}
if (!empty($debut)) {
    $where .= " AND t.date_transaction >= ?";
    $params[] = $debut;
}
if (!empty($fin)) {
    $where .= " AND t.date_transaction <= ?";
    $params[] = $fin;
}

$sql = "
    SELECT t.*,
           COALESCE(c.nom_prenom_client, t.destinataire) AS nom_client,
           f.titre_facture
    FROM transactions t
    LEFT JOIN clients c ON t.destinataire = c.code_client
    LEFT JOIN factures f ON t.code_facture = f.code_facture
    $where
    ORDER BY t.date_transaction DESC, t.heure_transaction DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Données pour les filtres
$factures = $pdo->query("SELECT code_facture, titre_facture FROM factures ORDER BY titre_facture")->fetchAll(PDO::FETCH_ASSOC);
$clients  = $pdo->query("SELECT code_client, nom_prenom_client FROM clients ORDER BY nom_prenom_client")->fetchAll(PDO::FETCH_ASSOC);
$types    = $pdo->query("SELECT DISTINCT type_transaction FROM transactions WHERE type_transaction IS NOT NULL AND type_transaction != '' ORDER BY type_transaction")->fetchAll(PDO::FETCH_COLUMN);

$user_name = "Jean Dupont";
$user_role = "Administrateur";
$user_photo = "https://via.placeholder.com/160x160/007bff/ffffff?text=JD";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Soutra+ | Transactions Globales</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .user-panel img { width: 2.1rem; height: 2.1rem; object-fit: cover; border-radius: 50%; }
        .nav-sidebar .nav-link { border-radius: 0.25rem; }
        .card { transition: all 0.2s; }
        .card:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .badge-type { font-size: 0.85rem; padding: 0.4em 0.8em; }
        .table-hover tbody tr:hover { background-color: #f8f9fa; }
        .filter-card { border-left: 5px solid #007bff; }
        .mode-btn { min-width: 140px; }
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
                        <h1><i class="fas fa-exchange-alt text-primary"></i> Transactions Globales</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Accueil</a></li>
                            <li class="breadcrumb-item active">Transactions</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card filter-card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title"><i class="fas fa-filter"></i> Filtrer les transactions</h3>
                    </div>
                    <div class="card-body">
                        <form method="get" class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label text-white fw-bold bg-primary px-2 py-1 rounded">Mode de filtrage</label>
                                <div class="btn-group w-100" role="group">
                                    <a href="?mode=facture" class="btn <?= $mode === 'facture' ? 'btn-light' : 'btn-outline-light' ?> mode-btn">Par Facture</a>
                                    <a href="?mode=client" class="btn <?= $mode === 'client' ? 'btn-light' : 'btn-outline-light' ?> mode-btn">Par Client</a>
                                    <a href="?mode=type" class="btn <?= $mode === 'type' ? 'btn-light' : 'btn-outline-light' ?> mode-btn">Par Type</a>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">
                                    <?= $mode === 'facture' ? 'Facture' : ($mode === 'client' ? 'Client' : 'Type') ?>
                                </label>
                                <?php if ($mode === 'facture'): ?>
                                    <select name="facture" class="form-select">
                                        <option value="">Toutes les factures</option>
                                        <?php foreach ($factures as $f): ?>
                                            <option value="<?= htmlspecialchars($f['code_facture']) ?>" <?= $facture === $f['code_facture'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($f['titre_facture']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php elseif ($mode === 'client'): ?>
                                    <select name="client" class="form-select">
                                        <option value="">Tous les clients</option>
                                        <?php foreach ($clients as $c): ?>
                                            <option value="<?= $c['code_client'] ?>" <?= $client == $c['code_client'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($c['nom_prenom_client']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else: ?>
                                    <select name="type" class="form-select">
                                        <option value="">Tous les types</option>
                                        <?php foreach ($types as $t): ?>
                                            <option value="<?= htmlspecialchars($t) ?>" <?= $type === $t ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($t) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-bold">Date début</label>
                                <input type="date" name="debut" class="form-control" value="<?= htmlspecialchars($debut) ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Date fin</label>
                                <input type="date" name="fin" class="form-control" value="<?= htmlspecialchars($fin) ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-light w-100 fw-bold">
                                    <i class="fas fa-search"></i> Filtrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if (empty($transactions)): ?>
                    <div class="alert alert-info text-center py-5">
                        <i class="fas fa-info-circle fa-3x mb-3"></i><br>
                        Aucune transaction trouvée avec les critères sélectionnés.
                    </div>
                <?php else: ?>
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h4 class="mb-0">
                                <i class="fas fa-list"></i> Résultats 
                                <span class="badge bg-primary ms-2"><?= count($transactions) ?> transaction<?= count($transactions) > 1 ? 's' : '' ?></span>
                            </h4>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Date & Heure</th>
                                            <th>N° Transaction</th>
                                            <th>Montant</th>
                                            <th>Type</th>
                                            <th>Client</th>
                                            <th>Facture</th>
                                            <th>État</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($transactions as $t): ?>
                                            <?php
                                            $etatClass = match($t['etat_transaction']) {
                                                'Succès' => 'success',
                                                'Echec', 'Échec' => 'danger',
                                                default => 'warning'
                                            };
                                            ?>
                                            <tr>
                                                <td><?= date('d/m/Y H:i', strtotime($t['date_transaction'] . ' ' . $t['heure_transaction'])) ?></td>
                                                <td><strong><?= htmlspecialchars($t['numero_transaction']) ?></strong></td>
                                                <td class="text-end fw-bold text-success"><?= formatMoney($t['montant_total']) ?></td>
                                                <td><span class="badge bg-info badge-type"><?= htmlspecialchars($t['type_transaction']) ?></span></td>
                                                <td><?= htmlspecialchars($t['nom_client'] ?? $t['destinataire']) ?></td>
                                                <td><?= htmlspecialchars($t['titre_facture'] ?? $t['code_facture'] ?? '-') ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $etatClass ?>"><?= htmlspecialchars($t['etat_transaction']) ?></span>
                                                </td>
                                                <td>
                                                    <a href="impression?numero=<?= urlencode($t['numero_transaction']) ?>"
                                                       class="btn btn-sm btn-success" target="_blank" title="Imprimer le reçu">
                                                        <i class="fas fa-print"></i> Reçu
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>© 2025 <a href="#">Soutra+</a>.</strong> Tous droits réservés.
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 2.0</div>
    </footer>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>