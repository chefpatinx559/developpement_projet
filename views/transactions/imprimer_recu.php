<?php
// views/reservations/imprimer_recu_reservation.php
ob_start();
require_once __DIR__ . '/../../database/database.php';

// CHARGEMENT DE FPDF + SUPPORT UTF-8 PARFAIT
require_once __DIR__ . '/../../libraries/fpdf/fpdf.php';
require_once __DIR__ . '/../../libraries/fpdf/makefont/makefont.php'; // si tu as des polices perso
define('FPDF_FONTPATH', __DIR__ . '/../../libraries/fpdf/font/');


function formatMoney($amount) {
    return number_format((float)$amount, 0, ',', ' ') . ' FCFA';
}

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
?>