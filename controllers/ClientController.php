<?php
// controllers/ClientControllers.php

class Clients
{
    private $pdo;

    public function __construct()
    {
        try {
            $this->pdo = new PDO(
                'mysql:host=localhost;dbname=app_hotel','root','',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }

    // === SUPPRESSION ===
    public function delete()
    {
        if (isset($_GET['delete'])) {
            try {
                $stmt = $this->pdo->prepare("DELETE FROM clients WHERE code_client = ?");
                $stmt->execute([$_GET['delete']]);
                $_SESSION['message'] = "Client supprimé avec succès.";
            } catch (Exception $e) {
                $_SESSION['message'] = "Erreur : " . $e->getMessage();
            }
        }
       
    }

    // === TRAITEMENT (AJOUT / MODIFICATION) ===
    public function traitement()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            
        }

        $action = $_POST['action'] ?? '';
        $code = trim($_POST['code_client']);
        $data = [
            'nom_prenom_client' => trim($_POST['nom_prenom_client']),
            'date_naissance_client' => $_POST['date_naissance_client'],
            'lieu_naissance_client' => trim($_POST['lieu_naissance_client']),
            'sexe_client' => $_POST['sexe_client'],
            'nationalite_client' => trim($_POST['nationalite_client']),
            'situation_matrimoniale_client' => trim($_POST['situation_matrimoniale_client']),
            'nombre_enfant_client' => (int)$_POST['nombre_enfant_client'],
            'telephone_client' => trim($_POST['telephone_client']),
            'email_client' => trim($_POST['email_client']),
            'pays_client' => trim($_POST['pays_client']),
            'ville_client' => trim($_POST['ville_client']),
            'adresse_client' => trim($_POST['adresse_client']),
            'quartier_client' => trim($_POST['quartier_client']),
            'type_client' => $_POST['type_client'],
            'etat_client' => $_POST['etat_client']
        ];

        try {
            if ($action === 'add') {
                $sql = "INSERT INTO clients VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $this->pdo->prepare($sql)->execute([
                    $code, $data['nom_prenom_client'], $data['date_naissance_client'], $data['lieu_naissance_client'],
                    $data['sexe_client'], $data['nationalite_client'], $data['situation_matrimoniale_client'],
                    $data['nombre_enfant_client'], $data['telephone_client'], $data['email_client'],
                    $data['pays_client'], $data['ville_client'], $data['adresse_client'], $data['quartier_client'],
                    $data['type_client'], $data['etat_client']
                ]);
                $_SESSION['message'] = "Client ajouté avec succès.";
            }

            if ($action === 'update') {
                $sql = "UPDATE clients SET 
                        nom_prenom_client=?, date_naissance_client=?, lieu_naissance_client=?,
                        sexe_client=?, nationalite_client=?, situation_matrimoniale_client=?,
                        nombre_enfant_client=?, telephone_client=?, email_client=?,
                        pays_client=?, ville_client=?, adresse_client=?, quartier_client=?,
                        type_client=?, etat_client=? 
                        WHERE code_client=?";
                $this->pdo->prepare($sql)->execute([
                    $data['nom_prenom_client'], $data['date_naissance_client'], $data['lieu_naissance_client'],
                    $data['sexe_client'], $data['nationalite_client'], $data['situation_matrimoniale_client'],
                    $data['nombre_enfant_client'], $data['telephone_client'], $data['email_client'],
                    $data['pays_client'], $data['ville_client'], $data['adresse_client'], $data['quartier_client'],
                    $data['type_client'], $data['etat_client'], $code
                ]);
                $_SESSION['message'] = "Client modifié avec succès.";
            }
        } catch (Exception $e) {
            $_SESSION['message'] = "Erreur : " . $e->getMessage();
        }

        
    }

    // === ENREGISTREMENT / LISTE ===
    public function enregistrement()
    {
        // === INITIALISATION DES VARIABLES ===
        $message = $_SESSION['message'] ?? '';
        // $alert_type = str_starts_with($message, 'Erreur') ? 'danger' : 'success';
        if ($message) unset($_SESSION['message']);

        $recherche = $_GET['recherche'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $where = $recherche ? "WHERE nom_prenom_client LIKE :search OR code_client LIKE :search OR telephone_client LIKE :search" : "";
        $search = "%$recherche%";

        // === COMPTE TOTAL ===
        $countSql = "SELECT COUNT(*) FROM clients $where";
        $countStmt = $this->pdo->prepare($countSql);
        if ($recherche) $countStmt->bindParam(':search', $search);
        $countStmt->execute();
        $total = $countStmt->fetchColumn();
        $totalPages = ceil($total / $perPage);

        // === LISTE CLIENTS + RÉSERVATIONS ===
        $sql = "SELECT c.*, COUNT(r.numero_reservation) as nb_reservations
                FROM clients c
                LEFT JOIN reservations r ON c.code_client = r.code_client
                $where
                GROUP BY c.code_client
                LIMIT ? OFFSET ?";
        $stmt = $this->pdo->prepare($sql);
        if ($recherche) $stmt->bindParam(':search', $search);
        $stmt->bindValue(1, $perPage, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $clients = $stmt->fetchAll();

        // === TOP CLIENT ===
        $topSql = "SELECT c.nom_prenom_client, COUNT(r.numero_reservation) as total
                   FROM clients c
                   LEFT JOIN reservations r ON c.code_client = r.code_client
                   GROUP BY c.code_client
                   ORDER BY total DESC
                   LIMIT 1";
        $topStmt = $this->pdo->query($topSql);
        $topClient = $topStmt->fetch() ?: ['nom_prenom_client' => 'Aucun', 'total' => 0];

        // === INCLURE LA VUE AVEC TOUTES LES VARIABLES ===
        include "views/clients/enregistrement-client.php";
    }


    public function liste() {
  

    

        include "views/clients/liste-client.php";
    }



}
?>