<?php
require "./database/database.php";

// Inclusion FPDF uniquement pour l'export PDF
if (isset($_POST['export']) && $_POST['export'] === 'pdf') {
    require_once './librairiesfpdf/fpdf/fpdf.php';
}

// ==================== EXPORT EXCEL / CSV / PDF ====================
if (isset($_POST['export']) && in_array($_POST['export'], ['excel', 'csv', 'pdf'])) {

    $stmt = $pdo->query("
        SELECT t.*, 
               c.nom_prenom_client, 
               f.titre_facture, 
               u.nom_prenom as nom_utilisateur
        FROM transactions t
        LEFT JOIN clients c ON t.destinataire = c.code_client
        LEFT JOIN factures f ON t.code_facture = f.code_facture
        LEFT JOIN utilisateurs u ON t.utilisateur_id = u.utilisateur_id
        ORDER BY t.date_transaction DESC, t.heure_transaction DESC
    ");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (ob_get_level()) ob_end_clean();

    // ====================== CSV ======================
    if ($_POST['export'] === 'csv') {
        $filename = 'Transactions_' . date('d-m-Y_H-i') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');
        fputcsv($output, ['N°','Date','Heure','Montant','Frais','Total','Type','Client','Facture','Utilisateur','Mode','État'], ';');

        foreach ($data as $row) {
            fputcsv($output, [
                $row['numero_transaction'],
                $row['date_transaction'],
                $row['heure_transaction'],
                $row['montant_transaction'],
                $row['frais_transaction'],
                $row['montant_total'],
                $row['type_transaction'],
                $row['nom_prenom_client'] ?? $row['destinataire'] ?? '',
                $row['titre_facture'] ?? $row['code_facture'] ?? '',
                $row['nom_utilisateur'] ?? $row['utilisateur_id'] ?? '',
                $row['mode_reglement'],
                $row['etat_transaction']
            ], ';');
        }
        exit;
    }

    // ====================== EXCEL ======================
    if ($_POST['export'] === 'excel') {
        $filename = 'Transactions_' . date('d-m-Y_H-i') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo "\xEF\xBB\xBF";
        ?>
        <table border="1">
            <tr style="background:#007bff;color:white;font-weight:bold;">
                <th>N° Transaction</th><th>Date</th><th>Heure</th><th>Montant</th><th>Frais</th><th>Total</th>
                <th>Type</th><th>Client</th><th>Facture</th><th>Utilisateur</th><th>Mode</th><th>État</th>
            </tr>
            <?php foreach ($data as $row): ?>
            <tr align="center">
                <td><?= htmlspecialchars($row['numero_transaction']) ?></td>
                <td><?= htmlspecialchars($row['date_transaction']) ?></td>
                <td><?= htmlspecialchars($row['heure_transaction']) ?></td>
                <td><?= number_format($row['montant_transaction']) ?></td>
                <td><?= number_format($row['frais_transaction']) ?></td>
                <td><?= number_format($row['montant_total']) ?></td>
                <td><?= htmlspecialchars($row['type_transaction']) ?></td>
                <td><?= htmlspecialchars($row['nom_prenom_client'] ?? $row['destinataire'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['titre_facture'] ?? $row['code_facture'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['nom_utilisateur'] ?? $row['utilisateur_id'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['mode_reglement']) ?></td>
                <td><?= htmlspecialchars($row['etat_transaction'] ?? $row['etat_transaction']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php
        exit;
    }

    // ====================== PDF (CORRIGÉ ET PROPRE) ======================
if ($_POST['export'] === 'pdf') {
    // 1. On nettoie TOUTE sortie précédente
    if (ob_get_level()) ob_end_clean();

    // 2. On charge FPDF seulement ici
    require_once './librairiesfpdf/fpdf/fpdf.php';

    // 3. On récupère les données
    $stmt = $pdo->query("
        SELECT t.*, 
               COALESCE(c.nom_prenom_client, t.destinataire, '—') as client,
               COALESCE(f.titre_facture, t.code_facture, 'Aucune') as facture,
               COALESCE(u.nom_prenom, t.utilisateur_id, 'Système') as utilisateur
        FROM transactions t
        LEFT JOIN clients c ON t.destinataire = c.code_client
        LEFT JOIN factures f ON t.code_facture = f.code_facture
        LEFT JOIN utilisateurs u ON t.utilisateur_id = u.utilisateur_id
        ORDER BY t.date_transaction DESC, t.heure_transaction DESC
    ");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Création du PDF
    $pdf = new FPDF('L', 'mm', 'A4');
    $pdf->AddPage();
    $pdf->SetAutoPageBreak(true, 15);

    // Titre
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->SetFillColor(0, 123, 255);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 15, mb_convert_encoding('Liste des Transactions', 'Windows-1252', 'UTF-8'), 0, 1, 'C', true);
    
    $pdf->Ln(5);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, mb_convert_encoding('Généré le ' . date('d/m/Y à H:i'), 'Windows-1252', 'UTF-8'), 0, 1, 'R');
    $pdf->Ln(8);

    // En-tête tableau
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetFillColor(230, 230, 230);
    $pdf->SetTextColor(0, 0, 0);

    $header = ['N°', 'Date/Heure', 'Montant', 'Frais', 'Total', 'Type', 'Client/Destinataire', 'Facture', 'Utilisateur', 'État'];
    $widths = [20, 28, 25, 20, 25, 30, 50, 40, 30, 25];

    foreach ($header as $i => $h) {
        $pdf->Cell($widths[$i], 10, mb_convert_encoding($h, 'Windows-1252', 'UTF-8'), 1, 0, 'C', true);
    }
    $pdf->Ln();

    // Données
    $pdf->SetFont('Arial', '', 9);
    foreach ($data as $row) {
        $dateHeure = date('d/m/Y H:i', strtotime($row['date_transaction'] . ' ' . $row['heure_transaction']));

        $pdf->Cell($widths[0], 8, $row['numero_transaction'], 1, 0, 'C');
        $pdf->Cell($widths[1], 8, mb_convert_encoding($dateHeure, 'Windows-1252', 'UTF-8'), 1, 0, 'C');
        $pdf->Cell($widths[2], 8, number_format($row['montant_transaction']), 1, 0, 'R');
        $pdf->Cell($widths[3], 8, number_format($row['frais_transaction'] ?? 0), 1, 0, 'R');
        $pdf->Cell($widths[4], 8, number_format($row['montant_total']), 1, 0, 'R');
        $pdf->Cell($widths[5], 8, mb_convert_encoding($row['type_transaction'] ?? '', 'Windows-1252', 'UTF-8'), 1, 0, 'C');
        $pdf->Cell($widths[6], 8, mb_convert_encoding($row['client'], 'Windows-1252', 'UTF-8'), 1, 0, 'L');
        $pdf->Cell($widths[7], 8, mb_convert_encoding($row['facture'], 'Windows-1252', 'UTF-8'), 1, 0, 'L');
        $pdf->Cell($widths[8], 8, mb_convert_encoding($row['utilisateur'], 'Windows-1252', 'UTF-8'), 1, 0, 'C');
        $pdf->Cell($widths[9], 8, mb_convert_encoding($row['etat_transaction'] ?? 'En attente', 'Windows-1252', 'UTF-8'), 1, 1, 'C');
    }

    // Envoi du PDF
    $filename = 'Transactions_' . date('d-m-Y_H-i') . '.pdf';
    $pdf->Output('D', $filename);
    exit;
}
}

// ==================== AFFICHAGE NORMAL ====================
$stmt = $pdo->query("SELECT t.*, 
           c.nom_prenom_client, 
           f.titre_facture, 
           u.nom_prenom as nom_utilisateur
    FROM transactions t
    LEFT JOIN clients c ON t.destinataire = c.code_client
    LEFT JOIN factures f ON t.code_facture = f.code_facture
    LEFT JOIN utilisateurs u ON t.utilisateur_id = u.utilisateur_id
    ORDER BY t.date_transaction DESC, t.heure_transaction DESC
");
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hotelio | Transactions</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6/css/all.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php include './config/dashboard.php'; ?>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <h1 class="mb-0">Transactions (<?= count($transactions) ?> enregistrées)</h1>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">

                <!-- Boutons Export -->
                <form method="post" class="d-flex justify-content-end mb-4 gap-2 flex-wrap">
                    <button type="submit" name="export" value="excel" class="btn btn-success">
                        Excel
                    </button>&nbsp &nbsp
                    <button type="submit" name="export" value="csv" class="btn btn-info text-white">
                        CSV
                    </button>&nbsp &nbsp
                    <button type="submit" name="export" value="pdf" class="btn btn-danger">
                        PDF
                    </button>&nbsp &nbsp
                </form>

                <!-- Chaque transaction dans une belle card -->
                <?php foreach ($transactions as $t): ?>
                <div class="card mb-4 shadow-sm border-start border-primary border-5">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            Transaction N° <strong><?= htmlspecialchars($t['numero_transaction']) ?></strong>
                            <small class="ms-3">
                                le <?= date('d/m/Y à H:i', strtotime($t['date_transaction'] . ' ' . $t['heure_transaction'])) ?>
                            </small>
                        </h5>
                        <div>
                            <span class="badge bg-light text-dark fs-6 fw-bold text-success">
                                <?= number_format($t['montant_total']) ?> FCFA
                            </span>
                            <span class="badge bg-<?= 
                                $t['etat_transaction'] === 'Succès' ? 'success' : 
                                ($t['etat_transaction'] === 'Echec' ? 'danger' : 'warning') ?> ms-2">
                                <?= $t['etat_transaction'] ?? 'En attente' ?>
                            </span>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Type</th>
                                        <th>Montant</th>
                                        <th>Frais</th>
                                        <th>Total</th>
                                        <th>Client / Destinataire</th>
                                        <th>Facture liée</th>
                                        <th>Mode règlement</th>
                                        <th>Enregistré par</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="text-center">
                                        <td><span class="badge bg-info text-dark"><?= htmlspecialchars($t['type_transaction'] ?? '') ?></span></td>
                                        <td><?= number_format($t['montant_transaction']) ?> FCFA</td>
                                        <td><?= number_format($t['frais_transaction'] ?? 0) ?> FCFA</td>
                                        <td class="text-success fw-bold fs-5"><?= number_format($t['montant_total']) ?> FCFA</td>
                                        <td><?= htmlspecialchars($t['nom_prenom_client'] ?? $t['destinataire'] ?? '—') ?></td>
                                        <td><?= htmlspecialchars($t['titre_facture'] ?? $t['code_facture'] ?? 'Aucune') ?></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($t['mode_reglement'] ?? '') ?></span></td>
                                        <td><?= htmlspecialchars($t['nom_utilisateur'] ?? $t['utilisateur_id'] ?? 'Système') ?></td>
                                        <td>
                                            <a href="impression.php?numero=<?= urlencode($t['numero_transaction']) ?>" 
                                               class="btn btn-success btn-sm" target="_blank">
                                                Reçu
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if (empty($transactions)): ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-receipt fa-3x mb-3"></i><br>
                        Aucune transaction enregistrée pour le moment.
                    </div>
                <?php endif; ?>

            </div>
        </section>
    </div>
</div>
</body>
</html>