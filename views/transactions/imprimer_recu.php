<?php
<<<<<<< HEAD
// views/transactions/imprimer_recu.php
ob_start();
require_once __DIR__ . '/../../database/database.php';
require_once __DIR__ . '/../../libraries/fpdf/fpdf.php';
=======
// views/reservations/imprimer_recu_reservation.php
ob_start();
require_once __DIR__ . '/../../database/database.php';

// CHARGEMENT DE FPDF + SUPPORT UTF-8 PARFAIT
require_once __DIR__ . '/../../libraries/fpdf/fpdf.php';
require_once __DIR__ . '/../../libraries/fpdf/makefont/makefont.php'; // si tu as des polices perso
define('FPDF_FONTPATH', __DIR__ . '/../../libraries/fpdf/font/');

>>>>>>> 24653d20902f480a272f396807e06cb4679ae919

function formatMoney($amount) {
    return number_format((float)$amount, 0, ',', ' ') . ' FCFA';
}

<<<<<<< HEAD
$numero = $_GET['numero'] ?? '';

// === MODE TEST ===
if (!$numero || $numero === 'TEST') {
    $trans = [
        'numero_transaction' => 'TEST-2025-001',
        'date_transaction' => date('Y-m-d'),
        'heure_transaction' => date('H:i:s'),
        'montant_transaction' => 250000,
        'frais_transaction' => 3000,
        'montant_total' => 253000,
        'type_transaction' => 'Paiement Réservation',
        'mode_reglement' => 'Orange Money',
        'client' => 'Mme. ADJOUA Marie-Claire',
        'telephone_client' => '+225 05 06 77 88 99',
        'caissier' => 'KOFFI Amenan'
    ];
} else {
    try {
        $stmt = $pdo->prepare("
            SELECT t.*,
                   COALESCE(c.nom_prenom_client, t.destinataire) AS client,
                   c.telephone_client,
                   u.nom_prenom AS caissier
            FROM transactions t
            LEFT JOIN clients c ON t.destinataire = c.code_client
            LEFT JOIN utilisateurs u ON t.utilisateur_id = u.utilisateur_id
            WHERE t.numero_transaction = ?
        ");
        $stmt->execute([$numero]);
        $trans = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$trans) die('Transaction introuvable');
    } catch (Exception $e) {
        die('Erreur : ' . $e->getMessage());
    }
}

// === CLASSE PDF AVEC RoundedRect INCLUSE ===
class PDF extends FPDF
{
    // === FONCTION RoundedRect COMPATIBLE FPDF CLASSIQUE ===
    function RoundedRect($x, $y, $w, $h, $r, $style = '')
    {
        $k = $this->k;
        $hp = $this->h;
        if($style=='F')
            $op='f';
        elseif($style=='FD' || $style=='DF')
            $op='B';
        else
            $op='S';
        $MyArc = 4/3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m', ($x+$r)*$k, ($hp-$y)*$k ));
        $xc = $x+$w-$r; $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k, ($hp-$y)*$k ));
        $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
        $xc = $x+$w-$r; $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l', ($x+$w)*$k, ($hp-$yc)*$k));
        $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
        $xc = $x+$r; $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k, ($hp-($y+$h))*$k));
        $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
        $xc = $x+$r; $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', ($x)*$k, ($hp-$yc)*$k ));
        $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
    {
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', 
            $x1*$this->k, ($h-$y1)*$this->k,
            $x2*$this->k, ($h-$y2)*$this->k,
            $x3*$this->k, ($h-$y3)*$this->k));
    }

    function Header()
    {
        // Fond dégradé simulé
        $this->SetFillColor(0, 70, 130);
        $this->Rect(0, 0, 210, 40, 'F');
        $this->SetFillColor(0, 100, 180);
        $this->Rect(0, 0, 210, 15, 'F');

        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 26);
        $this->SetY(12);
        $this->Cell(0, 10, 'SOUTRA+', 0, 1, 'C');

        $this->SetFont('Arial', '', 11);
        $this->SetTextColor(220, 230, 255);
        $this->Cell(0, 6, 'Hôtel & Résidences de Luxe • Cocody - Riviera Golf • Abidjan', 0, 1, 'C');
        $this->Cell(0, 6, 'Tél : +225 27 22 33 44 55 • contact@soutra.ci • www.soutra.ci', 0, 1, 'C');

        // Ligne dorée
        $this->SetDrawColor(255, 215, 0);
        $this->SetLineWidth(1.5);
        $this->Line(20, 45, 190, 45);
        $this->Ln(15);
    }

    function Footer()
    {
        $this->SetY(-45);
        $this->SetDrawColor(255, 215, 0);
        $this->SetLineWidth(0.8);
        $this->Line(20, $this->GetY(), 190, $this->GetY());
        $this->Ln(8);
        $this->SetFont('Arial', 'I', 10);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 8, 'Merci pour votre confiance et votre fidélité', 0, 1, 'C');
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 8, 'Reçu généré le ' . date('d/m/Y à H:i'), 0, 0, 'C');
    }
}

$pdf = new PDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetMargins(20, 20, 20);

// Titre principal
$pdf->SetFont('Arial', 'B', 20);
$pdf->SetTextColor(0, 70, 130);
$pdf->Cell(0, 15, 'REÇU OFFICIEL DE PAIEMENT', 0, 1, 'C');
$pdf->Ln(8);

// Infos transaction
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(55, 10, 'N° Transaction :', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(0, 90, 170);
$pdf->Cell(0, 10, $trans['numero_transaction'], 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(55, 10, 'Date & Heure :', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 10, date('d F Y à H:i', strtotime($trans['date_transaction'] . ' ' . $trans['heure_transaction'])), 0, 1);
$pdf->Ln(10);

// Bloc client avec coins arrondis
$pdf->RoundedRect(20, $pdf->GetY(), 170, 35, 8, 'D');
$pdf->SetFillColor(240, 248, 255);
$pdf->Rect(20, $pdf->GetY(), 170, 35, 'F');

$pdf->SetY($pdf->GetY() + 5);
$pdf->SetFont('Arial', 'B', 13);
$pdf->SetTextColor(0, 80, 150);
$pdf->Cell(0, 8, 'CLIENT', 0, 1, 'L');

$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(50, 50, 50);
$pdf->Cell(50, 8, '   Nom complet :', 0, 0);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, $trans['client'] ?? 'Non renseigné', 0, 1);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(50, 8, '   Téléphone :', 0, 0);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, $trans['telephone_client'] ?? '—', 0, 1);
$pdf->Ln(10);

// Tableau des montants
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(0, 80, 150);
$pdf->Cell(0, 10, 'DÉTAIL DU PAIEMENT', 0, 1, 'L');

$pdf->SetFillColor(0, 100, 180);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(110, 12, ' Libellé', 1, 0, 'L', true);
$pdf->Cell(60, 12, ' Montant', 1, 1, 'R', true);

$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(110, 11, ' Montant principal', 1, 0, 'L');
$pdf->Cell(60, 11, formatMoney($trans['montant_transaction']), 1, 1, 'R');

$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(110, 11, ' Frais de transaction', 1, 0, 'L');
$pdf->Cell(60, 11, formatMoney($trans['frais_transaction']), 1, 1, 'R');

// Total mis en évidence
$pdf->SetFillColor(0, 120, 200);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(110, 14, ' TOTAL PAYÉ', 1, 0, 'L', true);
$pdf->SetFont('Arial', 'B', 18);
$pdf->Cell(60, 14, formatMoney($trans['montant_total']), 1, 1, 'R', true);
$pdf->Ln(15);

// Infos complémentaires
$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(70, 70, 70);
$pdf->Cell(55, 10, 'Type d\'opération :', 0, 0);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, $trans['type_transaction'] ?? '—', 0, 1);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(55, 10, 'Mode de paiement :', 0, 0);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, $trans['mode_reglement'] ?? '—', 0, 1);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(55, 10, 'Encaissé par :', 0, 0);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, $trans['caissier'] ?? 'Système', 0, 1);
$pdf->Ln(20);

$pdf->SetFont('Arial', 'I', 11);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell(0, 10, 'Cachet de l\'établissement & Signature', 0, 1, 'R');

// Sortie du PDF
ob_end_clean();
$pdf->Output('I', 'Recu_' . $trans['numero_transaction'] . '.pdf');
=======
$id = $_GET['id'] ?? '';

if (!$id || $id === 'TEST') {
    $res = [
        'numero_reservation' => 'RES-2025-0118',
        'date_reservation'   => '2025-11-18',
        'heure_reservation'  => '20:03:00',
        'date_debut'         => '2025-11-20',
        'date_fin'           => '2025-11-21',
        'client_nom'         => 'Mme. ADJOUA Marie-Claire',
        'client_telephone'   => '+225 05 06 77 88 99',
        'chambre'            => 'Suite Deluxe N°208',
        'prix_journalier'    => 50000,
        'duree_jours'        => 1,
        'montant_total'      => 50000,
        'caissier'           => 'KOFFI Amenan'
    ];
} else {
    $stmt = $pdo->prepare("
        SELECT r.*,
               COALESCE(c.nom_prenom_client, 'Client inconnu') AS client_nom,
               c.telephone_client,
               ch.designation_chambre AS chambre,
               u.nom_prenom AS caissier
        FROM reservations r
        LEFT JOIN clients c ON r.client_id = c.client_id
        LEFT JOIN chambres ch ON r.chambre_id = ch.chambre_id
        LEFT JOIN utilisateurs u ON r.utilisateur_id = u.utilisateur_id
        WHERE r.reservation_id = ?
    ");
    $stmt->execute([$id]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$res) die('Réservation introuvable');
}

// CLASSE PDF AVEC UTF-8 PARFAIT
class PDF extends FPDF
{
    function Header()
    {
        $this->SetFillColor(0, 122, 255);
        $this->Rect(0, 0, 80, 22, 'F');

        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial','B',16);
        $this->SetY(6);
        $this->Cell(80, 8, utf8_decode('SOUTRA+'), 0, 1, 'C');

        $this->SetFont('Arial','',9);
        $this->Cell(80, 6, utf8_decode('Réservation confirmée'), 0, 1, 'C');
        $this->Ln(6);
    }

    function ligne($label, $value = '', $bold = false)
    {
        $this->SetFont('Arial', '', 8);
        $this->Cell(32, 6, utf8_decode($label), 0, 0);

        $this->SetFont('Arial', $bold ? 'B' : '', 9);
        $this->Cell(38, 6, utf8_decode($value), 0, 1, 'R');
    }

    function separateur()
    {
        $this->Ln(4);
        $this->SetDrawColor(220,220,220);
        $this->Line(4, $this->GetY(), 76, $this->GetY());
        $this->Ln(6);
    }

    function bigAmount($text)
    {
        $this->SetFont('Arial','B',22);
        $this->SetTextColor(0, 122, 255);
        $this->Cell(72, 14, utf8_decode($text), 0, 1, 'C');
    }
}

$pdf = new PDF('P', 'mm', [80, 300]);
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);
$pdf->SetMargins(4, 4, 4);

// MONTANT TOTAL
$pdf->bigAmount(formatMoney($res['montant_total']));
$pdf->Ln(6);

// CONTENU
$pdf->SetTextColor(0,0,0);

$pdf->ligne('N° Réservation', $res['numero_reservation'], true);
$pdf->ligne('Date résavation', date('d/m/Y à H:i', strtotime($res['date_reservation'].' '.$res['heure_reservation'])));
$pdf->separateur();

$pdf->ligne('Client', $res['client_nom'], true);
$pdf->ligne('Téléphone', $res['client_telephone'] ?? '-');
$pdf->separateur();

$pdf->ligne('Chambre', $res['chambre'] ?? 'Non assignée', true);
$pdf->ligne('Arrivée', date('d/m/Y', strtotime($res['date_debut'])));
$pdf->ligne('Départ', date('d/m/Y', strtotime($res['date_fin'])));
$pdf->ligne('Durée', $res['duree_jours'] . ' jour(s)');
$pdf->ligne('Tarif journalier', formatMoney($res['prix_journalier']));
$pdf->separateur();

// TOTAL FINAL
$pdf->SetFont('Arial','B',14);
$pdf->SetTextColor(0, 122, 255);
$pdf->Cell(34, 10, utf8_decode('TOTAL PAYÉ'), 0, 0);
$pdf->SetFont('Arial','B',18);
$pdf->Cell(38, 10, formatMoney($res['montant_total']), 0, 1, 'R');

$pdf->Ln(8);
$pdf->SetFont('Arial','',9);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(72, 6, 'Paiement par Wave', 0, 1, 'C');
$pdf->Cell(72, 6, utf8_decode('Encaissé par : ') . $res['caissier'], 0, 1, 'C');

$pdf->Ln(12);
$pdf->SetFont('Arial','I',8);
$pdf->SetTextColor(100,100,100);
$pdf->Cell(72, 5, utf8_decode('Merci pour votre réservation !'), 0, 1, 'C');
$pdf->Cell(72, 5, utf8_decode('Reçu édité le ') . date('d/m/Y à H:i'), 0, 1, 'C');

ob_end_clean();
$pdf->Output('I', 'Reservation_'.$res['numero_reservation'].'.pdf');
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
?>