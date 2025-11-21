<?php
// views/reservations/imprimer_recu_reservation.php
ob_start();
require_once __DIR__ . '/../../database/database.php';
require_once __DIR__ . '/../../libraries/fpdf/fpdf.php';

$numero = $_GET['numero'] ?? null;
$numero = trim($numero);
if (empty($numero)) {
    die("<h3 style='color:red;text-align:center;'>ERREUR : Numéro manquant</h3>");
}

$stmt = $pdo->prepare("
    SELECT
        r.numero_reservation,
        r.date_debut,
        r.date_fin,
        r.prix_chambre,
        r.duree_jours,
        r.montant_reservation,
        COALESCE(c.nom_prenom_client, 'Client inconnu') AS client_nom,
        c.telephone_client,
        COALESCE(ch.type_chambre, 'Non assignée') AS chambre_type
    FROM reservations r
    LEFT JOIN clients c ON r.code_client = c.code_client
    LEFT JOIN chambres ch ON r.code_chambre = ch.code_chambre
    WHERE r.numero_reservation = ?
");
$stmt->execute([$numero]);
$res = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$res) die("Réservation non trouvée");

// === FONCTION UTF-8 UNIVERSELLE (identique au modèle facture) ===
function utf8($text) {
    return iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $text);
}

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Courier','B',13);
        $this->Cell(0,6,utf8('SOUTRA +'),0,1,'C');
        $this->SetFont('Courier','',8);
        $this->Cell(0,4,utf8('Hôtel de luxe - Côte d’Ivoire '),0,1,'C');
        $this->Cell(0,4,utf8('Email : soutrapro531@gmail.com'),0,1,'C');
        $this->Ln(2);
        $this->SetLineWidth(0.5);
        $this->Line(6, $this->GetY(), 74, $this->GetY());
        $this->Ln(3);
    }
    function Footer() {
        $this->SetY(-12);
        $this->SetFont('Courier','I',7);
        $this->Cell(0,4,utf8('Merci pour votre confiance !'),0,0,'C');
    }
    // Cell avec UTF-8 automatique (identique au modèle facture)
    function C($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false) {
        $this->Cell($w, $h, utf8($txt), $border, $ln, $align, $fill);
    }
}

if (ob_get_length()) ob_clean();
$pdf = new PDF('P', 'mm', [80, 140]); // ← EXACTEMENT la même hauteur que les factures
$pdf->AddPage();
$pdf->SetMargins(6, 5, 6);
$pdf->SetAutoPageBreak(true, 10);

// Titre + date impression
$pdf->SetFont('Courier','B',11);
$pdf->C(0,6,'RÉSERVATION N° '.strtoupper($res['numero_reservation']),0,1,'C');
$pdf->SetFont('Courier','',8);
$pdf->C(0,4,'Imprimé le '.date('d/m/Y à H:i'),0,1,'C');
$pdf->Ln(2);

// Client + téléphone
$nom = $res['client_nom'];
$pdf->SetFont('Courier','B',9);
$pdf->C(0,5,'Client :',0,1,'L');
$pdf->SetFont('Courier','',9);
$pdf->C(0,5,$nom,0,1,'L');
if (!empty($res['telephone_client'])) {
    $pdf->SetFont('Courier','',8);
    $pdf->C(0,4,'Tél : '.$res['telephone_client'],0,1,'L');
}
$pdf->Ln(2);
$pdf->SetLineWidth(0.4);
$pdf->Line(6, $pdf->GetY(), 74, $pdf->GetY());
$pdf->Ln(3);

// Détails réservation (tout est conservé, juste compacté)
$pdf->SetFont('Courier','',9);
$pdf->C(48,5,'Chambre',0,0,'L');
$pdf->C(22,5,$res['chambre_type'],0,1,'R');

$pdf->C(48,5,'Arrivée',0,0,'L');
$pdf->C(22,5,date('d/m/Y', strtotime($res['date_debut'])),0,1,'R');

$pdf->C(48,5,'Départ',0,0,'L');
$pdf->C(22,5,date('d/m/Y', strtotime($res['date_fin'])),0,1,'R');

$pdf->C(48,5,'Durée',0,0,'L');
$pdf->C(22,5,$res['duree_jours'].' jour(s)',0,1,'R');

$pdf->C(48,5,'Tarif/jour',0,0,'L');
$pdf->C(22,5,number_format($res['prix_chambre'],0,',',' ').' FCFA',0,1,'R');

$pdf->Ln(2);
$pdf->SetLineWidth(0.7);
$pdf->Line(6, $pdf->GetY(), 74, $pdf->GetY());
$pdf->Ln(3);

// TOTAL
$pdf->SetFont('Courier','B',12);
$pdf->C(48,7,'TOTAL',0,0,'L');
$pdf->C(22,7,number_format($res['montant_reservation'],0,',',' ').' FCFA',0,1,'R');

ob_end_clean();
$pdf->Output('I', 'Reservation_'.$res['numero_reservation'].'.pdf');
exit;
?>