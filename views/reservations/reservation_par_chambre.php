<?php
<<<<<<< HEAD
require "database/database.php";

// Inclusion FPDF seulement si export PDF
if (isset($_POST['export']) && $_POST['export'] === 'pdf') {
    require_once 'librairiesfpdf/fpdf/fpdf.php';
}

// ==================== FILTRES ====================
$code_chambre = $_GET['code_chambre'] ?? '';
$date_debut   = $_GET['date_debut'] ?? '';
$date_fin     = $_GET['date_fin'] ?? '';

$where  = "WHERE 1=1";
$params = [];

if ($code_chambre !== '') {
    $where .= " AND r.code_chambre = ?";
    $params[] = $code_chambre;
}
if ($date_debut !== '' && $date_fin !== '') {
=======
//session_start();
require "database/database.php";

// ==================== FILTRE ====================
$code_chambre = $_GET['code_chambre'] ?? '';
$date_debut = $_GET['date_debut'] ?? '';
$date_fin = $_GET['date_fin'] ?? '';
$where = "WHERE 1=1";
$params = [];
if ($code_chambre) {
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
        ORDER BY ch.code_chambre, r.date_reservation DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (ob_get_level()) ob_end_clean();

    // CSV
    if ($_POST['export'] === 'csv') {
        $filename = 'Reservations_Chambre_' . date('d-m-Y_H-i') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo "\xEF\xBB\xBF";
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Hôtel','Chambre','N° Réserv.','Date','Client','Début','Fin','Durée','Montant','Statut'], ';');

        foreach ($data as $row) {
            fputcsv($output, [
                $row['nom_hotel'] ?? 'Inconnu',
                $row['nom_chambre'] ?? $row['code_chambre'],
                $row['numero_reservation'],
                date('d/m/Y', strtotime($row['date_reservation'])),
                $row['nom_prenom_client'] ?? '—',
                date('d/m/Y', strtotime($row['date_debut'])),
                date('d/m/Y', strtotime($row['date_fin'])),
                $row['duree_jours'],
                number_format($row['montant_reservation']) . ' FCFA',
                ucfirst($row['statut_reservation'] ?? '')
            ], ';');
        }
        exit;
    }

    // Excel
    if ($_POST['export'] === 'excel') {
        $filename = 'Reservations_Chambre_' . date('d-m-Y_H-i') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo "\xEF\xBB\xBF";
        ?>
        <table border="1">
            <tr style="background:#007bff;color:white;font-weight:bold;">
                <th>Hôtel</th><th>Chambre</th><th>N° Réserv.</th><th>Date</th><th>Client</th>
                <th>Début</th><th>Fin</th><th>Durée</th><th>Montant</th><th>Statut</th>
            </tr>
            <?php foreach ($data as $row): ?>
            <tr align="center">
                <td><?= htmlspecialchars($row['nom_hotel'] ?? 'Inconnu') ?></td>
                <td><?= htmlspecialchars($row['nom_chambre'] ?? $row['code_chambre']) ?></td>
                <td><?= htmlspecialchars($row['numero_reservation']) ?></td>
                <td><?= date('d/m/Y', strtotime($row['date_reservation'])) ?></td>
                <td><?= htmlspecialchars($row['nom_prenom_client'] ?? '—') ?></td>
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

    // PDF
    if ($_POST['export'] === 'pdf') {
        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetFillColor(0, 123, 255);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 12, 'Réservations par Chambre', 0, 1, 'C', true);
        $pdf->Ln(5);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 8, 'Généré le ' . date('d/m/Y à H:i'), 0, 1, 'R');
        $pdf->Ln(5);

        $currentChambre = '';
        foreach ($data as $row) {
            $chambre = ($row['nom_chambre'] ?? $row['code_chambre']) . ' — ' . ($row['nom_hotel'] ?? 'Hôtel inconnu');

            if ($currentChambre !== $chambre) {
                if ($currentChambre !== '') $pdf->Ln(8);
                $currentChambre = $chambre;

                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetFillColor(0, 123, 255);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->Cell(0, 10, $chambre, 0, 1, 'L', true);
                $pdf->Ln(3);

                $pdf->SetFillColor(220, 220, 220);
                $pdf->SetTextColor(0);
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(25, 8, 'N° Réserv.', 1, 0, 'C', true);
                $pdf->Cell(28, 8, 'Date', 1, 0, 'C', true);
                $pdf->Cell(55, 8, 'Client', 1, 0, 'C', true);
                $pdf->Cell(25, 8, 'Début', 1, 0, 'C', true);
                $pdf->Cell(25, 8, 'Fin', 1, 0, 'C', true);
                $pdf->Cell(15, 8, 'Jours', 1, 0, 'C', true);
                $pdf->Cell(30, 8, 'Montant', 1, 1, 'C', true);
            }

            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(25, 8, $row['numero_reservation'], 1, 0, 'C');
            $pdf->Cell(28, 8, date('d/m/Y', strtotime($row['date_reservation'])), 1, 0, 'C');
            $pdf->Cell(55, 8, mb_substr($row['nom_prenom_client'] ?? '—', 0, 28), 1, 0, 'L');
            $pdf->Cell(25, 8, date('d/m/Y', strtotime($row['date_debut'])), 1, 0, 'C');
            $pdf->Cell(25, 8, date('d/m/Y', strtotime($row['date_fin'])), 1, 0, 'C');
            $pdf->Cell(15, 8, $row['duree_jours'], 1, 0, 'C');
            $pdf->Cell(30, 8, number_format($row['montant_reservation']) . ' FCFA', 1, 1, 'C');
        }
        $pdf->Output('D', 'Reservations_Chambre_' . date('d-m-Y_H-i') . '.pdf');
        exit;
    }
}

// ==================== DONNÉES AVEC FILTRES ====================
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
    ORDER BY ch.code_chambre, r.date_reservation DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$all = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Regroupement par chambre
$group = [];
foreach ($all as $r) {
    $code = $r['code_chambre'] ?? 'INCONNU';
    $nom  = $r['nom_chambre'] ?? 'Chambre inconnue';
    $hotel = $r['nom_hotel'] ?? 'Hôtel inconnu';
    $key  = $code . '|' . $nom; // clé unique

    $group[$key]['code_chambre'] = $code;
    $group[$key]['nom_chambre']  = $nom;
    $group[$key]['hotel']        = $hotel;
    $group[$key]['list'][]       = $r;
}

$chambres = $pdo->query("SELECT code_chambre, nom_chambre FROM chambres ORDER BY nom_chambre")->fetchAll();
?>

=======
// ==================== LISTE ====================
$stmt = $pdo->prepare("
    SELECT r.*, cl.nom_prenom_client, ch.nom_chambre, h.nom_hotel 
    FROM reservations r 
    LEFT JOIN clients cl ON r.code_client = cl.code_client 
    LEFT JOIN chambres ch ON r.code_chambre = ch.code_chambre 
    LEFT JOIN hotels h ON ch.code_hotel = h.code_hotel 
    $where 
    ORDER BY r.date_reservation DESC
");
$stmt->execute($params);
$reservations = $stmt->fetchAll();

$chambres = $pdo->query("SELECT code_chambre, nom_chambre FROM chambres ORDER BY nom_chambre")->fetchAll();

// ==================== UTILISATEUR CONNECTÉ ====================
$user_name = "Jean Dupont";
$user_role = "Administrateur";
$user_photo = "https://via.placeholder.com/160x160/007bff/ffffff?text=JD";
?>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<<<<<<< HEAD
    <title>Hotelio | Réservations par Chambre</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6/css/all.min.css">
    <style>
        .badge-reservé { background:#ffc107; color:#000; }
        .badge-occupé { background:#dc3545; color:#fff; }
        .badge-libre { background:#28a745; color:#fff; }
    </style>
=======
    <title>Soutra+ | Réservations par Chambre</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php include 'config/dashboard.php'; ?>
<<<<<<< HEAD

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <h1 class="mb-0">Réservations par Chambre</h1>
                <?php if ($code_chambre): ?>
                    <small class="text-muted">— Chambre sélectionnée : <?= htmlspecialchars(array_column(array_filter($chambres, fn($c) => $c['code_chambre'] === $code_chambre), 'nom_chambre')[0] ?? '') ?></small>
                <?php endif; ?>
                <?php if ($date_debut && $date_fin): ?>
                    <small class="text-muted d-block">Du <?= date('d/m/Y', strtotime($date_debut)) ?> au <?= date('d/m/Y', strtotime($date_fin)) ?></small>
                <?php endif; ?>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">

                <!-- Filtres + Exports -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-body">
                        <form method="get" class="row g-3 align-items-end mb-3">
                            <div class="col-md-5">
                                <label class="form-label">Chambre</label>
                                <select name="code_chambre" class="form-select">
                                    <option value="">Toutes les chambres</option>
                                    <?php foreach ($chambres as $ch): ?>
                                        <option value="<?= $ch['code_chambre'] ?>" <?= $code_chambre === $ch['code_chambre'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($ch['nom_chambre']) ?>
                                        </option>
=======
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Réservations par Chambre</h1>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <form method="get" class="row g-3">
                            <div class="col-md-4">
                                <select name="code_chambre" class="form-control">
                                    <option value="">-- Sélectionner une chambre --</option>
                                    <?php foreach ($chambres as $ch): ?>
                                        <option value="<?= $ch['code_chambre'] ?>" <?= $code_chambre == $ch['code_chambre'] ? 'selected' : '' ?>><?= htmlspecialchars($ch['nom_chambre']) ?></option>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
<<<<<<< HEAD
                                <label class="form-label">Date début</label>
                                <input type="date" name="date_debut" class="form-control" value="<?= htmlspecialchars($date_debut) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date fin</label>
                                <input type="date" name="date_fin" class="form-control" value="<?= htmlspecialchars($date_fin) ?>">
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                            </div>
                        </form>

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

                <!-- Réservations groupées par chambre -->
                <?php foreach ($group as $key => $c): ?>
                <div class="card mb-4 shadow-sm border-start border-success border-5">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <?= htmlspecialchars($c['nom_chambre']) ?> 
                            <small>(<?= $c['code_chambre'] ?> — <?= htmlspecialchars($c['hotel']) ?>)</small>
                        </h5>
                        <span class="badge bg-light text-dark"><?= count($c['list']) ?> réservation<?= count($c['list']) > 1 ? 's' : '' ?></span>
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
                                        <th>Période</th>
                                        <th>Durée</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($c['list'] as $i => $r): ?>
                                    <tr>
                                        <td><span class="badge rounded-pill <?= $i==0?'bg-warning':($i==1?'bg-secondary':'bg-dark') ?> text-white"><?= $i+1 ?></span></td>
                                        <td><strong><?= htmlspecialchars($r['numero_reservation']) ?></strong></td>
                                        <td><?= date('d/m/Y', strtotime($r['date_reservation'])) ?></td>
                                        <td><?= htmlspecialchars($r['nom_prenom_client'] ?? '—') ?></td>
                                        <td><?= date('d/m', strtotime($r['date_debut'])) ?> → <?= date('d/m/Y', strtotime($r['date_fin'])) ?></td>
                                        <td><span class="badge bg-info"><?= $r['duree_jours'] ?> j</span></td>
                                        <td class="text-success fw-bold"><?= number_format($r['montant_reservation']) ?> FCFA</td>
                                        <td>
                                            <span class="badge <?= $r['statut_reservation']==='libre'?'bg-success':($r['statut_reservation']==='occupé'?'bg-danger':'bg-warning') ?>">
                                                <?= ucfirst($r['statut_reservation'] ?? '') ?>
                                            </span>
                                        </td>
                                    </tr>
=======
                                <input type="date" name="date_debut" class="form-control" value="<?= htmlspecialchars($date_debut) ?>">
                            </div>
                            <div class="col-md-3">
                                <input type="date" name="date_fin" class="form-control" value="<?= htmlspecialchars($date_fin) ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">Filtrer</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Numéro</th>
                                        <th>Date Rés.</th>
                                        <th>Client</th>
                                        <th>Chambre</th>
                                        <th>Hôtel</th>
                                        <th>Statut</th>
                                        <th>Montant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reservations as $r): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($r['numero_reservation']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($r['date_reservation'])) ?></td>
                                            <td><?= htmlspecialchars($r['nom_prenom_client'] ?? '—') ?></td>
                                            <td><?= htmlspecialchars($r['nom_chambre'] ?? '—') ?></td>
                                            <td><?= htmlspecialchars($r['nom_hotel'] ?? '—') ?></td>
                                            <td><span class="badge bg-<?= $r['statut_reservation'] === 'libre' ? 'success' : ($r['statut_reservation'] === 'occupé' ? 'danger' : 'warning') ?>"><?= ucfirst($r['statut_reservation']) ?></span></td>
                                            <td><?= htmlspecialchars($r['montant_reservation']) ?> FCFA</td>
                                        </tr>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
<<<<<<< HEAD
                <?php endforeach; ?>

                <?php if (empty($group)): ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle fa-2x mb-3"></i><br>
                    Aucune réservation trouvée avec les critères sélectionnés.
                </div>
                <?php endif; ?>

            </div>
        </section>
    </div>
</div>
=======
            </div>
        </section>
    </div>
    <footer class="main-footer">
        <strong>© 2025 <a href="#">Soutra+</a>.</strong> Tous droits réservés.
    </footer>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
</body>
</html>