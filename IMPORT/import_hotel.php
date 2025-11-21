<?php
// ===================== CONFIGURATION BASE DE DONNÉES
$host = 'localhost';
$dbname = 'u738064605_soutra';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(Exception $e) {
    die("Erreur connexion : " . $e->getMessage());
}

$message = "";
$inserted = 0;
$updated = 0;
$errors = 0;

if (isset($_POST['importer'])) {
    if ($_FILES['fichier']['error'] == 0) {
        $file = $_FILES['fichier']['tmp_name'];

        require 'vendor/autoload.php'; // PhpSpreadsheet

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
            $worksheet   = $spreadsheet->getActiveSheet();

            $first = true; // Ignorer la première ligne (en-têtes)
            foreach ($worksheet->getRowIterator() as $row) {
                if ($first) { $first = false; continue; }

                $rowData = [];
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                foreach ($cellIterator as $cell) {
                    $rowData[] = $cell->getValue();
                }

                $data = array_map('trim', $rowData);

                // On accepte 12 ou 13 colonnes (observation_hotel et etat_hotel peuvent être vides)
                if (count($data) < 12 || empty($data[0])) {
                    $errors++;
                    continue;
                }

                $code_hotel         = $data[0];
                $nom_hotel          = $data[1];
                $type_hotel         = $data[2];
                $latitude_hotel     = $data[3] ?? null;
                $longitude_hotel    = $data[4] ?? null;
                $pays_hotel         = $data[5];
                $ville_hotel        = $data[6];
                $quartier_hotel     = $data[7];
                $adresse_hotel      = $data[8];
                $telephone_hotel    = $data[9];
                $email_hotel        = $data[10];
                $observation_hotel  = $data[11] ?? '';
                $etat_hotel         = $data[12] ?? 'Actif';

                // Insertion ou mise à jour (clé primaire ou unique sur code_hotel)
                $sql = "INSERT INTO hotels 
                    (code_hotel, nom_hotel, type_hotel, latitude_hotel, longitude_hotel,
                     pays_hotel, ville_hotel, quartier_hotel, adresse_hotel, telephone_hotel,
                     email_hotel, observation_hotel, etat_hotel)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                     nom_hotel         = VALUES(nom_hotel),
                     type_hotel        = VALUES(type_hotel),
                     latitude_hotel    = VALUES(latitude_hotel),
                     longitude_hotel   = VALUES(longitude_hotel),
                     pays_hotel        = VALUES(pays_hotel),
                     ville_hotel       = VALUES(ville_hotel),
                     quartier_hotel    = VALUES(quartier_hotel),
                     adresse_hotel     = VALUES(adresse_hotel),
                     telephone_hotel   = VALUES(telephone_hotel),
                     email_hotel       = VALUES(email_hotel),
                     observation_hotel = VALUES(observation_hotel),
                     etat_hotel        = VALUES(etat_hotel)";

                $stmt = $pdo->prepare($sql);

                $stmt->bindValue(1,  $code_hotel,         PDO::PARAM_STR);
                $stmt->bindValue(2,  $nom_hotel,          PDO::PARAM_STR);
                $stmt->bindValue(3,  $type_hotel,         PDO::PARAM_STR);
                $stmt->bindValue(4,  $latitude_hotel,     PDO::PARAM_STR);
                $stmt->bindValue(5,  $longitude_hotel,    PDO::PARAM_STR);
                $stmt->bindValue(6,  $pays_hotel,         PDO::PARAM_STR);
                $stmt->bindValue(7,  $ville_hotel,        PDO::PARAM_STR);
                $stmt->bindValue(8,  $quartier_hotel,     PDO::PARAM_STR);
                $stmt->bindValue(9,  $adresse_hotel,      PDO::PARAM_STR);
                $stmt->bindValue(10, $telephone_hotel,    PDO::PARAM_STR);
                $stmt->bindValue(11, $email_hotel,        PDO::PARAM_STR);
                $stmt->bindValue(12, $observation_hotel,  PDO::PARAM_STR);
                $stmt->bindValue(13, $etat_hotel,         PDO::PARAM_STR);

                if ($stmt->execute()) {
                    $updated += $stmt->rowCount() > 1 ? 1 : 0;
                    $inserted += $stmt->rowCount() == 1 ? 1 : 0;
                } else {
                    $errors++;
                }
            }

            $message = "<div style='color:green;font-weight:bold;'>Import hôtels terminé ! $inserted ajoutés, $updated mis à jour, $errors erreurs.</div>";

        } catch (Exception $e) {
            $message = "<div style='color:red;'>Erreur fichier : " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    } else {
        $message = "<div style='color:red;'>Erreur lors de l'upload du fichier.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importer Hôtels depuis Excel</title>
    <style>
        body { font-family: Arial; background: #f4f6f9; padding: 40px; text-align: center; }
        .box { max-width: 600px; margin: auto; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        input[type="file"] { padding: 20px; border: 2px dashed #0066cc; width: 100%; border-radius: 10px; }
        button { margin-top: 20px; background: #0066cc; color: white; padding: 15px 40px; font-size: 18px; border: none; border-radius: 10px; cursor: pointer; }
        button:hover { background: #0052a3; }
        a { color: #0066cc; text-decoration: underline; }
    </style>
</head>
<body>
<div class="box">
    <h1>Importer Hôtels depuis Excel / CSV</h1>
    <?php if($message) echo $message; ?>
    <p>Formats acceptés : <strong>.xlsx</strong>, <strong>.xls</strong>, <strong>.csv</strong></p>
    <p><a href="modele_hotels.xlsx">Télécharger le modèle Excel parfait</a></p>

    <form method="post" enctype="multipart/form-data">
        <input type="file" name="fichier" accept=".xlsx,.xls,.csv" required>
        <br><br>
        <button type="submit" name="importer">Importer dans la base</button>
    </form>
</div>
</body>
</html>