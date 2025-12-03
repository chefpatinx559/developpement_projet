<?php
require "database/database.php";

$message   = "";
$inserted  = 0;
$updated   = 0;
$errors    = 0;

if (isset($_POST['importer'])) {
    if ($_FILES['fichier']['error'] == 0) {
        $file = $_FILES['fichier']['tmp_name'];
        require 'vendor/autoload.php';

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
                    $value = $cell->getValue();
                    // Important : convertir les NULL et les cellules vides en chaîne vide
                    $rowData[] = ($value === null || $value === '') ? '' : $value;
                }

                // Nettoyer avec trim uniquement sur les chaînes (plus de warning PHP 8.1+)
                $data = array_map(function($item) {
                    return is_string($item) ? trim($item) : $item;
                }, $rowData);

                // On attend au moins 7 colonnes
                if (count($data) < 7 || empty($data[0])) {
                    $errors++;
                    continue;
                }

                $code_chambre        = $data[0];
                $nom_chambre         = $data[1];
                $type_chambre        = $data[2];
                $description_chambre = $data[3] ?? '';
                $prix_chambre        = $data[4] ?? 0;
                $etat_chambre        = $data[5] ?? 'Disponible';
                $code_hotel          = $data[6];

                // Nettoyage du prix (gère 75 000, 75.000, 75000, etc.)
                $prix_chambre = str_replace([' ', ','], ['', '.'], $prix_chambre);
                $prix_chambre = is_numeric($prix_chambre) ? (float)$prix_chambre : 0;

                // Insertion / mise à jour
                $sql = "INSERT INTO chambres
                    (code_chambre, nom_chambre, type_chambre, description_chambre, prix_chambre, etat_chambre, code_hotel)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                     nom_chambre = VALUES(nom_chambre),
                     type_chambre = VALUES(type_chambre),
                     description_chambre = VALUES(description_chambre),
                     prix_chambre = VALUES(prix_chambre),
                     etat_chambre = VALUES(etat_chambre),
                     code_hotel = VALUES(code_hotel)";

                $stmt = $pdo->prepare($sql);

                $stmt->bindValue(1, $code_chambre,        PDO::PARAM_STR);
                $stmt->bindValue(2, $nom_chambre,         PDO::PARAM_STR);
                $stmt->bindValue(3, $type_chambre,        PDO::PARAM_STR);
                $stmt->bindValue(4, $description_chambre, PDO::PARAM_STR);
                $stmt->bindValue(5, $prix_chambre,        PDO::PARAM_STR);
                $stmt->bindValue(6, $etat_chambre,        PDO::PARAM_STR);
                $stmt->bindValue(7, $code_hotel,          PDO::PARAM_STR);

                if ($stmt->execute()) {
                    $updated += ($stmt->rowCount() > 1) ? 1 : 0;
                    $inserted += ($stmt->rowCount() == 1) ? 1 : 0;
                } else {
                    $errors++;
                }
            }

            $message = "<div style='color:green;font-weight:bold;'>Import terminé ! $inserted ajoutés, $updated mis à jour, $errors erreurs.</div>";

        } catch (Exception $e) {
            $message = "<div style='color:red;'>Erreur lors du chargement du fichier : " . htmlspecialchars($e->getMessage()) . "</div>";
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
    <title>Importer Chambres depuis Excel</title>
    <style>
        body { font-family: Arial; background: #f4f6f9; padding: 40px; text-align: center; }
        .box { max-width: 600px; margin: auto; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        input[type="file"] { padding: 20px; border: 2px dashed #0066cc; width: 100%; border-radius: 10px; }
        button { margin-top: 20px; background: #0066cc; color: white; padding: 15px 40px; font-size: 18px; border: none; border-radius: 10px; cursor: pointer; }
        button:hover { background: #0052a3; }
    </style>
</head>
<body>
<div class="box">
    <h1>Importer Chambres depuis Excel</h1>
    <?php if($message) echo $message; ?>
    <p>Formats acceptés : <strong>.xlsx</strong>, <strong>.xls</strong>, <strong>.csv</strong></p>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="fichier" accept=".xlsx,.xls,.csv" required>
        <br>
        <button type="submit" name="importer">Importer dans la base</button>
    </form>
</div>
</body>
</html>