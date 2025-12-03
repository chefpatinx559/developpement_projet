<?php
require "database/database.php";

// Inclusion FPDF seulement si on exporte en PDF
if (isset($_POST['export']) && $_POST['export'] === 'pdf') {
    require_once 'librairiesfpdf/fpdf/fpdf.php'; 
}

// ==================== EXPORT EXCEL / CSV / PDF ====================
if (isset($_POST['export']) && in_array($_POST['export'], ['excel', 'csv', 'pdf'])) {

    $stmt = $pdo->query("
        SELECT c.*, h.nom_hotel, h.code_hotel 
        FROM chambres c 
        LEFT JOIN hotels h ON c.code_hotel = h.code_hotel 
        ORDER BY h.code_hotel, c.prix_chambre + 0
    ");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Nettoyer le buffer
    if (ob_get_level()) ob_end_clean();

    // ====================== EXPORT CSV ======================
    if ($_POST['export'] === 'csv') {
        $filename = 'Chambres_' . date('d-m-Y_H-i') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo "\xEF\xBB\xBF"; // BOM UTF-8 pour Excel

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Hôtel','Code Hôtel','Code Chambre','Nom Chambre','Type','Prix (FCFA)','État'], ';');

        foreach ($data as $row) {
            fputcsv($output, [
                $row['nom_hotel'] ?? 'Inconnu',
                $row['code_hotel'] ?? '',
                $row['code_chambre'],
                $row['nom_chambre'],
                ucwords(str_replace('chambre ', '', $row['type_chambre'] ?? '')),
                $row['prix_chambre'],
                ucfirst($row['etat_chambre'] ?? '')
            ], ';');
        }
        exit;
    }

    // ====================== EXPORT EXCEL (.xls) ======================
    if ($_POST['export'] === 'excel') {
        $filename = 'Chambres_' . date('d-m-Y_H-i') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo "\xEF\xBB\xBF";
        ?>
        <table border="1">
            <tr style="background:#007bff;color:white;font-weight:bold;">
                <th>Hôtel</th><th>Code Hôtel</th><th>Code Chambre</th><th>Nom Chambre</th><th>Type</th><th>Prix (FCFA)</th><th>État</th>
            </tr>
            <?php foreach ($data as $row): ?>
            <tr align="center">
                <td><?= htmlspecialchars($row['nom_hotel'] ?? 'Inconnu') ?></td>
                <td><?= htmlspecialchars($row['code_hotel'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['code_chambre']) ?></td>
                <td><?= htmlspecialchars($row['nom_chambre']) ?></td>
                <td><?= htmlspecialchars(ucwords(str_replace('chambre ', '', $row['type_chambre'] ?? ''))) ?></td>
                <td><?= htmlspecialchars($row['prix_chambre']) ?></td>
                <td><?= htmlspecialchars(ucfirst($row['etat_chambre'] ?? '')) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php
        exit;
    }

    // ====================== EXPORT PDF avec FPDF ======================
    if ($_POST['export'] === 'pdf') {
        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetFillColor(0, 123, 255);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 12, 'Liste des Chambres par Hotel', 0, 1, 'C', true);
        $pdf->Ln(5);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 8, 'Genere le ' . date('d/m/Y à H:i'), 0, 1, 'R');
        $pdf->Ln(5);

        $currentHotel = '';
        $pdf->SetFont('Arial', '', 10);

        foreach ($data as $row) {
            $hotel = ($row['nom_hotel'] ?? 'Hôtel inconnu') . ' (' . ($row['code_hotel'] ?? 'INCONNU') . ')';

            // Nouveau hôtel → titre + en-tête tableau
            if ($currentHotel !== $hotel) {
                if ($currentHotel !== '') {
                    $pdf->Ln(8);
                }
                $currentHotel = $hotel;

                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetFillColor(0, 123, 255);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->Cell(0, 10, $hotel, 0, 1, 'L', true);
                $pdf->Ln(3);

                // En-tête du tableau
                $pdf->SetFillColor(220, 220, 220);
                $pdf->SetTextColor(0);
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(12, 10, 'N°', 1, 0, 'C', true);
                $pdf->Cell(30, 10, 'Code', 1, 0, 'C', true);
                $pdf->Cell(55, 10, 'Nom Chambre', 1, 0, 'C', true);
                $pdf->Cell(40, 10, 'Type', 1, 0, 'C', true);
                $pdf->Cell(25, 10, 'Prix', 1, 0, 'C', true);
                $pdf->Cell(28, 10, 'Etat', 1, 1, 'C', true);
            }

            // Ligne de données
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(12, 9, 1, 1, 0, 'C');
            $pdf->Cell(30, 9, $row['code_chambre'], 1, 0, 'C');
            $pdf->Cell(55, 9, $row['nom_chambre'], 1, 0, 'L');
            $pdf->Cell(40, 9, ucwords(str_replace('chambre ', '', $row['type_chambre'] ?? '')), 1, 0, 'C');
            $pdf->Cell(25, 9, number_format($row['prix_chambre']) . ' FCFA', 1, 0, 'C');
            $etat = ucfirst($row['etat_chambre'] ?? '');
            $pdf->Cell(28, 9, $etat, 1, 1, 'C');
        }

        $pdf->Output('D', 'Chambres_' . date('d-m-Y_H-i') . '.pdf');
        exit;
    }
    exit;
}

// ==================== AFFICHAGE NORMAL DE LA PAGE ====================
$stmt = $pdo->query("
    SELECT c.*, h.nom_hotel, h.code_hotel 
    FROM chambres c 
    LEFT JOIN hotels h ON c.code_hotel = h.code_hotel 
    ORDER BY h.code_hotel, c.prix_chambre + 0
");
$all = $stmt->fetchAll(PDO::FETCH_ASSOC);

$group = [];
foreach ($all as $c) {
    $code = $c['code_hotel'] ?? 'INCONNU';
    $group[$code]['nom'] = $c['nom_hotel'] ?? 'Hôtel inconnu';
    $group[$code]['list'][] = $c;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hotelio | Chambres</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6/css/all.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php include 'config/dashboard.php'; ?>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <h1 class="mb-0">Chambres par Hôtel (Triées par prix)</h1>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">

                <!-- BOUTONS D'EXPORT -->
                <form method="post" class="d-flex justify-content-end mb-4 gap-2 flex-wrap">
                    <button type="submit" name="export" value="excel" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>&nbsp &nbsp
                    <button type="submit" name="export" value="csv" class="btn btn-info text-white">
                        <i class="fas fa-file-csv"></i> CSV
                    </button>&nbsp &nbsp
                    <button type="submit" name="export" value="pdf" class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>&nbsp &nbsp
                    <a  href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/chambre/import" class="btn btn-default">
                        <i class="fas fa-file-excel"></i> Importer
                    </a>

                </form>

                <!-- Liste des chambres groupées -->
                <?php foreach ($group as $code => $h): ?>
                <div class="card mb-4 shadow-sm border-start border-primary border-5">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?= htmlspecialchars($h['nom']) ?> <small>(<?= $code ?>)</small></h5>
                        <span class="badge bg-light text-dark"><?= count($h['list']) ?> chambre<?= count($h['list']) > 1 ? 's' : '' ?></span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th><th>Code</th><th>Nom</th><th>Type</th><th>Prix</th><th>État</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($h['list'] as $i => $c): ?>
                                    <tr>
                                        <td><span class="badge rounded-pill <?= $i==0?'bg-warning':($i==1?'bg-secondary':'bg-danger') ?> text-white"><?= $i+1 ?></span></td>
                                        <td><strong><?= htmlspecialchars($c['code_chambre']) ?></strong></td>
                                        <td><?= htmlspecialchars($c['nom_chambre']) ?></td>
                                        <td><span class="badge bg-info text-dark"><?= ucwords(str_replace('chambre ','',$c['type_chambre']??'')) ?></span></td>
                                        <td class="text-success fw-bold"><?= number_format($c['prix_chambre']) ?> FCFA</td>
                                        <td>
                                            <span class="badge bg-<?= strtolower($c['etat_chambre'] ?? '') == 'disponible' ? 'success' : 'danger' ?>">
                                                <?= ucfirst($c['etat_chambre'] ?? '') ?>
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

            </div>
        </section>
    </div>
</div>
</body>
</html>