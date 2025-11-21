<?php
// session_start();
require_once "database/database.php";

function formatMoney($amount)
{
    return number_format((float)$amount, 0, ',', ' ') . ' FCFA';
}

// === RÉCUPÉRATION DES DATES (communes aux deux vues) ===
$debut = $_GET['debut'] ?? '';
$fin   = $_GET['fin']   ?? '';

// === ONGLETS : caisse ou clients ? ===
$tab = $_GET['tab'] ?? 'caisse'; // caisse | clients

// === 1. SOLDE CAISSE GLOBALE ===
if ($tab === 'caisse' || $tab === 'clients') {
    $where = [];
    $params = [];

    if (!empty($debut)) {
        $where[] = "date_transaction >= ?";
        $params[] = $debut;
    }
    if (!empty($fin)) {
        $where[] = "date_transaction <= ?";
        $params[] = $fin;
    }
    $where[] = "etat_transaction = 'Succès'";

    $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : "WHERE etat_transaction = 'Succès'";

    $sql = "SELECT COALESCE(SUM(montant_total), 0) AS total FROM transactions $whereSql";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $total_caisse = $stmt->fetchColumn();
}

// === 2. SOLDE PAR CLIENT ===
if ($tab === 'clients') {
    $where = "WHERE 1=1";
    $params = [];

    if (!empty($debut)) {
        $where .= " AND t.date_transaction >= ?";
        $params[] = $debut;
    }
    if (!empty($fin)) {
        $where .= " AND t.date_transaction <= ?";
        $params[] = $fin;
    }

    $sql = "
        SELECT
            c.nom_prenom_client,
            COALESCE(SUM(CASE WHEN t.type_transaction IN ('Paiement Réservation', 'Paiement Facture', 'paiement', 'Règlement') THEN t.montant_total ELSE 0 END), 0) AS total_paye,
            COALESCE(SUM(CASE WHEN t.type_transaction NOT IN ('Paiement Réservation', 'Paiement Facture', 'paiement', 'Règlement') AND t.type_transaction IS NOT NULL THEN t.montant_total ELSE 0 END), 0) AS total_du
        FROM clients c
        LEFT JOIN transactions t ON c.code_client = t.destinataire $where AND t.etat_transaction = 'Succès'
        GROUP BY c.code_client, c.nom_prenom_client
        HAVING total_paye > 0 OR total_du > 0
        ORDER BY c.nom_prenom_client
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $soldes_clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_paye_global = $total_du_global = 0;
    foreach ($soldes_clients as $s) {
        $total_paye_global += $s['total_paye'];
        $total_du_global   += $s['total_du'];
    }
    $solde_general = $total_paye_global - $total_du_global;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Soutra+ | Finances & Soldes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .nav-tabs .nav-link.active {
            background: #007bff !important;
            color: white !important;
            border-color: #007bff;
        }
        .card-solde {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .display-2 { font-size: 4.5rem; font-weight: 800; }
        .periode {
            background: rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 0.9rem;
        }
        .solde-credit { color: #27ae60; font-weight: 800; }
        .solde-debit { color: #e74c3c; font-weight: 800; }
        .table tr:hover { background-color: #f8f9fa; }
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
                        <h1>Finances & Soldes</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Accueil</a></li>
                            <li class="breadcrumb-item active">Finances</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">

                <!-- FILTRE DE DATE COMMUN -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title"><i class="fas fa-calendar-alt"></i> Période de référence</h3>
                    </div>
                    <div class="card-body">
                        <form method="get" class="row g-3 align-items-end">
                            <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
                            <div class="col-md-5">
                                <label class="form-label text-muted">Date début</label>
                                <input type="date" name="debut" class="form-control" value="<?= htmlspecialchars($debut) ?>">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label text-muted">Date fin</label>
                                <input type="date" name="fin" class="form-control" value="<?= htmlspecialchars($fin) ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Actualiser</button>
                            </div>
                        </form>
                        <?php if (!empty($debut) || !empty($fin)): ?>
                            <div class="mt-3 alert alert-info">
                                Période sélectionnée : 
                                <strong>
                                    <?= $debut ? 'Du ' . date('d/m/Y', strtotime($debut)) : 'Début' ?>
                                    <?= $fin ? ' au ' . date('d/m/Y', strtotime($fin)) : '' ?>
                                </strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ONGLETTS -->
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link <?= $tab === 'caisse' ? 'active' : '' ?>" 
                           href="?tab=caisse&debut=<?= urlencode($debut) ?>&fin=<?= urlencode($fin) ?>">
                            <i class="fas fa-cash-register"></i> Solde Caisse
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $tab === 'clients' ? 'active' : '' ?>" 
                           href="?tab=clients&debut=<?= urlencode($debut) ?>&fin=<?= urlencode($fin) ?>">
                            <i class="fas fa-users"></i> Solde par Client
                        </a>
                    </li>
                </ul>

                <!-- === VUE : SOLDE CAISSE === -->
                <?php if ($tab === 'caisse'): ?>
                    <div class="card card-solde text-center mb-4">
                        <div class="card-body py-5">
                            <h2 class="display-2 mb-3"><?= formatMoney($total_caisse) ?></h2>
                            <p class="lead opacity-90">Total encaissé (transactions réussies)</p>
                            <div class="mt-4">
                                <span class="badge bg-light text-dark fs-6 px-4 py-2">
                                    Mise à jour : <?= date('d/m/Y à H:i') ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Paiements réussis</span>
                                    <span class="info-box-number"><?= formatMoney($total_caisse) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Échecs / En attente</span>
                                    <span class="info-box-number">Non comptabilisés</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- === VUE : SOLDE PAR CLIENT === -->
                <?php if ($tab === 'clients'): ?>
                    <!-- Résumé général -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-arrow-down"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total encaissé</span>
                                    <span class="info-box-number"><?= formatMoney($total_paye_global) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-arrow-up"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total dû / dépensé</span>
                                    <span class="info-box-number"><?= formatMoney($total_du_global) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-<?= $solde_general >= 0 ? 'primary' : 'danger' ?>">
                                <span class="info-box-icon"><i class="fas fa-balance-scale"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Solde général</span>
                                    <span class="info-box-number <?= $solde_general >= 0 ? 'text-success' : 'text-danger' ?>">
                                        <strong><?= formatMoney(abs($solde_general)) ?></strong>
                                        <small><?= $solde_general >= 0 ? 'CRÉDIT' : 'DÉBIT' ?></small>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tableau détaillé -->
                    <div class="card shadow">
                        <div class="card-header bg-gradient-dark text-white">
                            <h3 class="card-title">
                                Détail par client (<?= count($soldes_clients) ?> client<?= count($soldes_clients)>1?'s':'' ?>)
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Client</th>
                                            <th class="text-end">Encaissé</th>
                                            <th class="text-end">Dû / Dépensé</th>
                                            <th class="text-center">Solde Final</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($soldes_clients as $s):
                                            $solde = $s['total_paye'] - $s['total_du'];
                                        ?>
                                            <tr>
                                                <td class="fw-bold"><?= htmlspecialchars($s['nom_prenom_client']) ?></td>
                                                <td class="text-end text-success fw-bold"><?= formatMoney($s['total_paye']) ?></td>
                                                <td class="text-end text-warning fw-bold"><?= formatMoney($s['total_du']) ?></td>
                                                <td class="text-center fs-5">
                                                    <?php if ($solde > 0): ?>
                                                        <span class="solde-credit">+ <?= formatMoney($solde) ?> <small>(crédit)</small></span>
                                                    <?php elseif ($solde < 0): ?>
                                                        <span class="solde-debit">- <?= formatMoney(abs($solde)) ?> <small>(débit)</small></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">Équilibré</span>
                                                    <?php endif; ?>
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
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>