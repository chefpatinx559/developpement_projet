<?php
<<<<<<< HEAD
require "database/database.php";

// Inclusion FPDF seulement pour l'export PDF
if (isset($_POST['export']) && $_POST['export'] === 'pdf') {
    require_once 'librairiesfpdf/fpdf/fpdf.php';
}

// ==================== FILTRES ====================
$code_hotel = $_GET['code_hotel'] ?? '';
$date_debut = $_GET['date_debut'] ?? '';
$date_fin   = $_GET['date_fin'] ?? '';

$where = "WHERE 1=1";
$params = [];

if ($code_hotel !== '') {
    $where .= " AND ch.code_hotel = ?";
    $params[] = $code_hotel;
}
if ($date_debut !== '' && $date_fin !== '') {
=======
// session_start();
require "database/database.php";

// ==================== DONNÉES COMMUNES ====================
$user_name = "Jean Dupont";
$user_role = "Administrateur";
$user_photo = "https://via.placeholder.com/160x160/007bff/ffffff?text=JD";

// Récupération des listes pour les filtres
$hotels   = $pdo->query("SELECT code_hotel, nom_hotel FROM hotels ORDER BY nom_hotel")->fetchAll();
$clients  = $pdo->query("SELECT code_client, nom_prenom_client FROM clients ORDER BY nom_prenom_client")->fetchAll();
$chambres = $pdo->query("SELECT code_chambre, nom_chambre FROM chambres ORDER BY nom_chambre")->fetchAll();

// ==================== PARAMÈTRES GET ====================
$view         = $_GET['view'] ?? 'hotel'; // hotel | client | chambre | top
$code_hotel   = $_GET['code_hotel'] ?? '';
$code_client  = $_GET['code_client'] ?? '';
$code_chambre = $_GET['code_chambre'] ?? '';
$date_debut   = $_GET['date_debut'] ?? '';
$date_fin     = $_GET['date_fin'] ?? '';

// Construction dynamique de la requête
$where  = "WHERE 1=1";
$params = [];

if ($view === 'hotel' && $code_hotel) {
    $where .= " AND ch.code_hotel = ?";
    $params[] = $code_hotel;
}
if ($view === 'client' && $code_client) {
    $where .= " AND r.code_client = ?";
    $params[] = $code_client;
}
if ($view === 'chambre' && $code_chambre) {
    $where .= " AND r.code_chambre = ?";
    $params[] = $code_chambre;
}
if ($date_debut && $date_fin) {
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
    $where .= " AND r.date_reservation BETWEEN ? AND ?";
    $params[] = $date_debut;
    $params[] = $date_fin;
}

<<<<<<< HEAD
// ==================== EXPORT (CSV / EXCEL / PDF) ====================
if (isset($_POST['export']) && in_array($_POST['export'], ['excel', 'csv', 'pdf'])) {

    $sql = "
        SELECT 
            r.*,
            cl.nom_prenom_client,
            ch.nom_chambre,
            ch.code_chambre,
            h.nom_hotel,
            h.code_hotel
        FROM reservations r
        LEFT JOIN clients cl ON r.code_client = cl.code_client
        LEFT JOIN chambres ch ON r.code_chambre = ch.code_chambre
        LEFT JOIN hotels h ON ch.code_hotel = h.code_hotel
        $where
        ORDER BY h.code_hotel, r.date_reservation DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Nettoyage buffer
    if (ob_get_level()) ob_end_clean();

    // ====================== CSV ======================
    if ($_POST['export'] === 'csv') {
        $filename = 'Reservations_' . date('d-m-Y_H-i') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo "\xEF\xBB\xBF"; // BOM UTF-8
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Hôtel','Code Hôtel','N° Réserv.','Date','Client','Chambre','Début','Fin','Durée','Montant','Statut'], ';');

        foreach ($data as $row) {
            fputcsv($output, [
                $row['nom_hotel'] ?? 'Inconnu',
                $row['code_hotel'] ?? '',
                $row['numero_reservation'],
                date('d/m/Y', strtotime($row['date_reservation'])),
                $row['nom_prenom_client'] ?? '—',
                $row['nom_chambre'] ?? $row['code_chambre'],
                date('d/m/Y', strtotime($row['date_debut'])),
                date('d/m/Y', strtotime($row['date_fin'])),
                $row['duree_jours'],
                number_format($row['montant_reservation']) . ' FCFA',
                ucfirst($row['statut_reservation'] ?? '')
            ], ';');
        }
        exit;
    }

    // ====================== EXCEL ======================
    if ($_POST['export'] === 'excel') {
        $filename = 'Reservations_' . date('d-m-Y_H-i') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo "\xEF\xBB\xBF";
        ?>
        <table border="1">
            <tr style="background:#007bff;color:white;font-weight:bold;">
                <th>Hôtel</th><th>N° Réserv.</th><th>Date</th><th>Client</th><th>Chambre</th><th>Début</th><th>Fin</th><th>Durée</th><th>Montant</th><th>Statut</th>
            </tr>
            <?php foreach ($data as $row): ?>
            <tr align="center">
                <td><?= htmlspecialchars($row['nom_hotel'] ?? 'Inconnu') ?></td>
                <td><?= htmlspecialchars($row['numero_reservation']) ?></td>
                <td><?= date('d/m/Y', strtotime($row['date_reservation'])) ?></td>
                <td><?= htmlspecialchars($row['nom_prenom_client'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['nom_chambre'] ?? $row['code_chambre']) ?></td>
                <td><?= date('d/m/Y', strtotime($row['date_debut'])) ?></td>
                <td><?= date('d/m/Y', strtotime($row['date_fin'])) ?></td>
                <td><?= $row['duree_jours'] ?></td>
                <td><?= number_format($row['montant_reservation']) ?> FCFA</td>
                <td><?= ucfirst($row['statut_reservation'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php
        exit;
    }

    // ====================== PDF ======================
    if ($_POST['export'] === 'pdf') {
        $pdf = new FPDF('L', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetFillColor(0, 123, 255);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 12, 'Liste des Réservations (Filtrées)', 0, 1, 'C', true);
        $pdf->Ln(5);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 8, 'Généré le ' . date('d/m/Y à H:i'), 0, 1, 'R');
        $pdf->Ln(5);

        $currentHotel = '';
        foreach ($data as $row) {
            $hotel = ($row['nom_hotel'] ?? 'Inconnu') . ' (' . ($row['code_hotel'] ?? '') . ')';
            if ($currentHotel !== $hotel) {
                if ($currentHotel !== '') $pdf->Ln(8);
                $currentHotel = $hotel;
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetFillColor(0, 123, 255);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->Cell(0, 10, $hotel, 0, 1, 'L', true);
                $pdf->Ln(3);

                $pdf->SetFillColor(220, 220, 220);
                $pdf->SetTextColor(0);
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(20, 8, 'N°', 1, 0, 'C', true);
                $pdf->Cell(30, 8, 'Réserv.', 1, 0, 'C', true);
                $pdf->Cell(30, 8, 'Date', 1, 0, 'C', true);
                $pdf->Cell(60, 8, 'Client', 1, 0, 'C', true);
                $pdf->Cell(40, 8, 'Chambre', 1, 0, 'C', true);
                $pdf->Cell(25, 8, 'Début', 1, 0, 'C', true);
                $pdf->Cell(25, 8, 'Fin', 1, 0, 'C', true);
                $pdf->Cell(20, 8, 'Jours', 1, 0, 'C', true);
                $pdf->Cell(30, 8, 'Montant', 1, 1, 'C', true);
            }
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(20, 8, '', 1, 0, 'C');
            $pdf->Cell(30, 8, $row['numero_reservation'], 1, 0, 'C');
            $pdf->Cell(30, 8, date('d/m/Y', strtotime($row['date_reservation'])), 1, 0, 'C');
            $pdf->Cell(60, 8, mb_substr($row['nom_prenom_client'] ?? '—', 0, 30), 1, 0, 'L');
            $pdf->Cell(40, 8, $row['nom_chambre'] ?? $row['code_chambre'], 1, 0, 'L');
            $pdf->Cell(25, 8, date('d/m/Y', strtotime($row['date_debut'])), 1, 0, 'C');
            $pdf->Cell(25, 8, date('d/m/Y', strtotime($row['date_fin'])), 1, 0, 'C');
            $pdf->Cell(20, 8, $row['duree_jours'], 1, 0, 'C');
            $pdf->Cell(30, 8, number_format($row['montant_reservation']) . ' FCFA', 1, 1, 'C');
        }
        $pdf->Output('D', 'Reservations_' . date('d-m-Y_H-i') . '.pdf');
        exit;
    }
}

// ==================== CHARGEMENT DONNÉES AVEC FILTRES ====================
$sql = "
    SELECT 
        r.*,
        cl.nom_prenom_client,
        ch.nom_chambre,
        ch.code_chambre,
        h.nom_hotel,
        h.code_hotel
    FROM reservations r
    LEFT JOIN clients cl ON r.code_client = cl.code_client
    LEFT JOIN chambres ch ON r.code_chambre = ch.code_chambre
    LEFT JOIN hotels h ON ch.code_hotel = h.code_hotel
    $where
    ORDER BY h.code_hotel, r.date_reservation DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$all = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Regroupement par hôtel
$group = [];
foreach ($all as $r) {
    $code = $r['code_hotel'] ?? 'INCONNU';
    $group[$code]['nom'] = $r['nom_hotel'] ?? 'Hôtel inconnu';
    $group[$code]['list'][] = $r;
}

$hotels = $pdo->query("SELECT code_hotel, nom_hotel FROM hotels ORDER BY nom_hotel")->fetchAll();
=======
// Ordre selon la vue
$order = match($view) {
    'top'   => "ORDER BY CAST(r.montant_reservation AS DECIMAL(10,2)) ASC",
    default => "ORDER BY r.date_reservation DESC"
};

$sql = "
    SELECT r.*, cl.nom_prenom_client, ch.nom_chambre, h.nom_hotel, h.code_hotel
    FROM reservations r
    LEFT JOIN clients cl   ON r.code_client = cl.code_client
    LEFT JOIN chambres ch  ON r.code_chambre = ch.code_chambre
    LEFT JOIN hotels h     ON ch.code_hotel = h.code_hotel
    $where $order
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$all_reservations = $stmt->fetchAll();

// Regroupement par hôtel pour la vue "hotel" et "top"
$reservations_par_hotel = [];
foreach ($all_reservations as $r) {
    $code = $r['code_hotel'] ?? 'INCONNU';
    $nom  = $r['nom_hotel'] ?? 'Hôtel non défini';
    if (!isset($reservations_par_hotel[$code])) {
        $reservations_par_hotel[$code] = [
            'nom' => $nom,
            'reservations' => []
        ];
    }
    $reservations_par_hotel[$code]['reservations'][] = $r;
}
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<<<<<<< HEAD
    <title>Hotelio | Réservations (Filtrées par Hôtel & Date)</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6/css/all.min.css">
    <style>
        .badge-reservé { background:#ffc107; color:#000; }
        .badge-occupé { background:#dc3545; color:#fff; }
        .badge-libre { background:#28a745; color:#fff; }
=======
    <title>Soutra+ | Réservations</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .user-panel img { width: 2.1rem; height: 2.1rem; object-fit: cover; }
        .nav-sidebar .nav-link { border-radius: 0.25rem; }
        .card { transition: transform 0.2s; }
        .card:hover { transform: translateY(-2px); }
        .hotel-card { border-left: 4px solid #007bff; }
        .badge-rank { width: 28px; height: 28px; font-size: 0.75rem; }
        .top-resa { background-color: #f8f9fa; }
        .tab-content { min-height: 500px; }
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
<<<<<<< HEAD
=======

>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
    <?php include 'config/dashboard.php'; ?>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
<<<<<<< HEAD
                <h1 class="mb-0">Réservations par Hôtel <?= $code_hotel ? '— ' . ($group[$code_hotel]['nom'] ?? '') : '' ?></h1>
                <?php if ($date_debut && $date_fin): ?>
                    <small class="text-muted">Du <?= date('d/m/Y', strtotime($date_debut)) ?> au <?= date('d/m/Y', strtotime($date_fin)) ?></small>
                <?php endif; ?>
=======
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Réservations - Vue détaillée</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Accueil</a></li>
                            <li class="breadcrumb-item active">Réservations</li>
                        </ol>
                    </div>
                </div>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">

<<<<<<< HEAD
                <!-- Filtres + Exports -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="get" class="row g-3 align-items-end mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Hôtel</label>
                                <select name="code_hotel" class="form-select">
                                    <option value="">Tous les hôtels</option>
                                    <?php foreach ($hotels as $h): ?>
                                        <option value="<?= $h['code_hotel'] ?>" <?= $code_hotel === $h['code_hotel'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($h['nom_hotel']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date début</label>
                                <input type="date" name="date_debut" class="form-control" value="<?= htmlspecialchars($date_debut) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date fin</label>
=======
                <!-- Onglets de navigation -->
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link <?= $view==='hotel' ? 'active' : '' ?>" href="?view=hotel">Par Hôtel</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $view==='client' ? 'active' : '' ?>" href="?view=client">Par Client</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $view==='chambre' ? 'active' : '' ?>" href="?view=chambre">Par Chambre</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $view==='top' ? 'active' : '' ?>" href="?view=top">Top Montants (croissant)</a>
                    </li>
                </ul>

                <!-- Filtres -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="get" class="row g-3 align-items-end">
                            <input type="hidden" name="view" value="<?= htmlspecialchars($view) ?>">

                            <?php if ($view === 'hotel'): ?>
                                <div class="col-md-4">
                                    <label>Hôtel</label>
                                    <select name="code_hotel" class="form-control">
                                        <option value="">Tous les hôtels</option>
                                        <?php foreach ($hotels as $h): ?>
                                            <option value="<?= $h['code_hotel'] ?>" <?= $code_hotel==$h['code_hotel']?'selected':'' ?>>
                                                <?= htmlspecialchars($h['nom_hotel']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>

                            <?php if ($view === 'client'): ?>
                                <div class="col-md-4">
                                    <label>Client</label>
                                    <select name="code_client" class="form-control">
                                        <option value="">Tous les clients</option>
                                        <?php foreach ($clients as $cl): ?>
                                            <option value="<?= $cl['code_client'] ?>" <?= $code_client==$cl['code_client']?'selected':'' ?>>
                                                <?= htmlspecialchars($cl['nom_prenom_client']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>

                            <?php if ($view === 'chambre'): ?>
                                <div class="col-md-4">
                                    <label>Chambre</label>
                                    <select name="code_chambre" class="form-control">
                                        <option value="">Toutes les chambres</option>
                                        <?php foreach ($chambres as $ch): ?>
                                            <option value="<?= $ch['code_chambre'] ?>" <?= $code_chambre==$ch['code_chambre']?'selected':'' ?>>
                                                <?= htmlspecialchars($ch['nom_chambre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>

                            <div class="col-md-3">
                                <label>Date début</label>
                                <input type="date" name="date_debut" class="form-control" value="<?= htmlspecialchars($date_debut) ?>">
                            </div>
                            <div class="col-md-3">
                                <label>Date fin</label>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                                <input type="date" name="date_fin" class="form-control" value="<?= htmlspecialchars($date_fin) ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                            </div>
                        </form>
<<<<<<< HEAD

                        <form method="post" class="d-flex justify-content-end gap-2 flex-wrap">
                            <button type="submit" name="export" value="excel" class="btn btn-success">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                            <button type="submit" name="export" value="csv" class="btn btn-info text-white">
                                <i class="fas fa-file-csv"></i> CSV
                            </button>
                            <button type="submit" name="export" value="pdf" class="btn btn-danger">
                                <i class="fas fa-file-pdf"></i> PDF
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Réservations groupées par hôtel -->
                <?php foreach ($group as $code => $h): ?>
                <div class="card mb-4 shadow-sm border-start border-primary border-5">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?= htmlspecialchars($h['nom']) ?> <small>(<?= $code ?>)</small></h5>
                        <span class="badge bg-light text-dark"><?= count($h['list']) ?> réservation<?= count($h['list']) > 1 ? 's' : '' ?></span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>N° Réserv.</th>
                                        <th>Date</th>
                                        <th>Client</th>
                                        <th>Chambre</th>
                                        <th>Période</th>
                                        <th>Durée</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($h['list'] as $i => $r): ?>
                                    <tr>
                                        <td><span class="badge rounded-pill <?= $i==0?'bg-warning':($i==1?'bg-secondary':'bg-dark') ?> text-white"><?= $i+1 ?></span></td>
                                        <td><strong><?= htmlspecialchars($r['numero_reservation']) ?></strong></td>
                                        <td><?= date('d/m/Y', strtotime($r['date_reservation'])) ?></td>
                                        <td><?= htmlspecialchars($r['nom_prenom_client'] ?? '—') ?></td>
                                        <td><?= htmlspecialchars($r['nom_chambre'] ?? $r['code_chambre']) ?></td>
                                        <td><?= date('d/m', strtotime($r['date_debut'])) ?> → <?= date('d/m/Y', strtotime($r['date_fin'])) ?></td>
                                        <td><span class="badge bg-info"><?= $r['duree_jours'] ?> j</span></td>
                                        <td class="text-success fw-bold"><?= number_format($r['montant_reservation']) ?> FCFA</td>
                                        <td>
                                            <span class="badge <?= $r['statut_reservation']==='libre'?'bg-success':($r['statut_reservation']==='occupé'?'bg-danger':'bg-warning') ?>">
                                                <?= ucfirst($r['statut_reservation'] ?? '') ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if (empty($group)): ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i> Aucune réservation trouvée avec les filtres actuels.
                </div>
=======
                    </div>
                </div>

                <!-- Contenu selon la vue -->
                <?php if ($view === 'hotel' || $view === 'top'): ?>
                    <?php if (empty($reservations_par_hotel)): ?>
                        <div class="alert alert-info text-center">Aucune réservation trouvée.</div>
                    <?php else: ?>
                        <?php foreach ($reservations_par_hotel as $code => $data): ?>
                            <div class="card hotel-card shadow-sm mb-4">
                                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="fas fa-hotel"></i> <?= htmlspecialchars($data['nom']) ?>
                                        <small class="text-light ms-2">(<?= $code ?>)</small>
                                    </h5>
                                    <span class="badge bg-light text-dark">
                                        <?= count($data['reservations']) ?> réservation<?= count($data['reservations'])>1?'s':'' ?>
                                    </span>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Numéro</th>
                                                    <th>Date</th>
                                                    <th>Client</th>
                                                    <th>Chambre</th>
                                                    <th>Statut</th>
                                                    <th class="text-end">Montant</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($data['reservations'] as $index => $r): ?>
                                                    <?php
                                                    $isTop = $view==='top' && $index < 3;
                                                    $rankClass = match($index){
                                                        0 => 'bg-warning text-dark',
                                                        1 => 'bg-secondary text-white',
                                                        2 => 'bg-danger text-white',
                                                        default => 'bg-light text-dark'
                                                    };
                                                    $badgeStatut = match($r['statut_reservation']){
                                                        'libre' => 'success',
                                                        'occupé','occupée' => 'danger',
                                                        default => 'warning'
                                                    };
                                                    ?>
                                                    <tr <?= $isTop ? 'class="top-resa"' : '' ?>>
                                                        <td>
                                                            <span class="badge rounded-pill <?= $rankClass ?> badge-rank d-flex align-items-center justify-content-center">
                                                                <?= $index + 1 ?>
                                                            </span>
                                                        </td>
                                                        <td><strong><?= htmlspecialchars($r['numero_reservation']) ?></strong></td>
                                                        <td><?= date('d/m/Y', strtotime($r['date_reservation'])) ?></td>
                                                        <td><?= htmlspecialchars($r['nom_prenom_client'] ?? '—') ?></td>
                                                        <td><?= htmlspecialchars($r['nom_chambre'] ?? '—') ?></td>
                                                        <td><span class="badge bg-<?= $badgeStatut ?>"><?= ucfirst($r['statut_reservation']) ?></span></td>
                                                        <td class="text-end text-success fw-bold">
                                                            <?= number_format((float)$r['montant_reservation']) ?> FCFA
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                <?php else: // vues client ou chambre (liste plate) ?>
                    <?php if (empty($all_reservations)): ?>
                        <div class="alert alert-info text-center">Aucune réservation trouvée.</div>
                    <?php else: ?>
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Numéro</th>
                                                <th>Date Rés.</th>
                                                <th>Client</th>
                                                <th>Chambre</th>
                                                <th>Hôtel</th>
                                                <th>Statut</th>
                                                <th class="text-end">Montant</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($all_reservations as $r):
                                                $badgeStatut = match($r['statut_reservation']){
                                                    'libre' => 'success',
                                                    'occupé','occupée' => 'danger',
                                                    default => 'warning'
                                                };
                                            ?>
                                                <tr>
                                                    <td><strong><?= htmlspecialchars($r['numero_reservation']) ?></strong></td>
                                                    <td><?= date('d/m/Y', strtotime($r['date_reservation'])) ?></td>
                                                    <td><?= htmlspecialchars($r['nom_prenom_client'] ?? '—') ?></td>
                                                    <td><?= htmlspecialchars($r['nom_chambre'] ?? '—') ?></td>
                                                    <td><?= htmlspecialchars($r['nom_hotel'] ?? '—') ?></td>
                                                    <td><span class="badge bg-<?= $badgeStatut ?>"><?= ucfirst($r['statut_reservation']) ?></span></td>
                                                    <td class="text-end text-success fw-bold">
                                                        <?= number_format((float)$r['montant_reservation']) ?> FCFA
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                <?php endif; ?>

            </div>
        </section>
    </div>
<<<<<<< HEAD
</div>
=======

    <footer class="main-footer">
        <strong>© 2025 <a href="#">Soutra+</a>.</strong> Tous droits réservés.
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
</body>
</html>