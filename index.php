<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soutra CI – Réservation Hôtelière</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root{
            --primary:#003580;--secondary:#0a3d78;--yellow:#ffcc00;--light:#f8f9fa;
            --success:#28a745;--warning:#ffc107;--danger:#dc3545;
        }
        body{font-family:'Roboto',sans-serif;background:var(--light);}
        .navbar-custom{background:var(--primary);padding:.75rem 1rem;box-shadow:0 2px 10px rgba(0,0,0,.1);}
        .navbar-custom .navbar-brand{color:#fff;font-weight:700;font-size:1.6rem;}
        .nav-link-custom{color:#fff!important;font-weight:500;margin:0 8px;padding:8px 16px!important;border-radius:20px;transition:.3s;}
        .nav-link-custom:hover,.nav-link-custom.active{background:rgba(255,255,255,.2);}
        .btn-signup{background:#fff;color:var(--primary);font-weight:600;border-radius:20px;padding:6px 16px;}
        .btn-login{background:transparent;color:#fff;border:1px solid #fff;border-radius:20px;padding:6px 16px;font-weight:500;}
        .hero-section{background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff;padding:80px 20px 60px;text-align:center;}
        .hero-section h1{font-size:3.2rem;font-weight:700;margin-bottom:1rem;}
        .hero-section p{font-size:1.25rem;margin-bottom:2.5rem;opacity:.95;}
        .search-form{background:#fff;border-radius:16px;padding:2rem;box-shadow:0 15px 40px rgba(0,0,0,.15);max-width:1000px;margin:auto;}
        .form-control-custom{border:none;border-bottom:2px solid #ddd;border-radius:0;padding:14px 0;font-size:1.1rem;transition:.3s;}
        .form-control-custom:focus{box-shadow:none;border-bottom-color:var(--yellow);}
        .form-label-custom{font-weight:500;color:#444;margin-bottom:8px;}
        .btn-search{background:var(--yellow);color:var(--primary);font-weight:700;border-radius:12px;padding:14px 40px;border:none;font-size:1.1rem;transition:.3s;}
        .btn-search:hover{background:#e6b800;transform:translateY(-2px);}
        .section-title{font-weight:700;color:#222;margin:4rem 0 1.5rem;font-size:2rem;position:relative;padding-bottom:10px;}
        .section-title::after{content:'';position:absolute;left:0;bottom:0;width:60px;height:4px;background:var(--yellow);border-radius:2px;}
        .hotel-card{border:none;border-radius:16px;overflow:hidden;box-shadow:0 6px 20px rgba(0,0,0,.1);transition:.3s;margin-bottom:2rem;background:#fff;}
        .hotel-card:hover{transform:translateY(-8px);box-shadow:0 15px 35px rgba(0,0,0,.15);}
        .hotel-img{height:220px;object-fit:cover;width:100%;}
        .hotel-info{padding:1.5rem;}
        .hotel-name{font-weight:700;color:var(--primary);margin-bottom:.5rem;font-size:1.3rem;}
        .hotel-location{color:#666;font-size:.95rem;margin-bottom:.75rem;}
        .room-type-badge{background:#e3f2fd;color:var(--primary);padding:6px 12px;border-radius:30px;font-size:.85rem;font-weight:500;display:inline-block;margin-bottom:1rem;}
        .price{font-weight:700;color:#222;font-size:1.5rem;}
        .price small{font-weight:400;color:#888;font-size:.9rem;}
        .status-badge{font-size:.85rem;padding:4px 10px;border-radius:20px;font-weight:500;}
        .status-available{background:#d4edda;color:var(--success);}
        .status-occupied{background:#f8d7da;color:var(--danger);}
        .status-reserved{background:#fff3cd;color:#856404;}
        .btn-reserve{background:var(--primary);color:#fff;border:none;padding:10px 20px;border-radius:8px;font-weight:600;}
        .btn-reserve:hover{background:var(--secondary);}
        .no-results{text-align:center;padding:3rem;color:#666;}
        footer{background:#f8f9fa;padding:4rem 0 2rem;margin-top:5rem;border-top:1px solid #eee;}
        .footer-link{color:#666;text-decoration:none;margin-bottom:.75rem;display:block;font-size:.95rem;}
        .footer-link:hover{color:var(--primary);}
        @media(max-width:768px){
            .hero-section h1{font-size:2.3rem;}
            .search-form{padding:1.5rem;}
            .btn-search{width:100%;margin-top:1rem;}
        }
    </style>
</head>
<body>

<?php
// session_start();

require "database/database.php";

// ---------- RECHERCHE ----------
$dest   = $_GET['destination'] ?? '';
$in     = $_GET['date_arrivee'] ?? '';
$out    = $_GET['date_depart'] ?? '';
$adult  = $_GET['adultes'] ?? 2;
$child  = $_GET['enfants'] ?? 0;
$rooms  = $_GET['chambres'] ?? 1;

// Construction de la requête (LEFT JOIN pour vérifier les conflits)
$sql = "
    SELECT 
        c.*,
        h.nom_hotel, h.ville_hotel, h.quartier_hotel, h.adresse_hotel, h.telephone_hotel,
        r.numero_reservation AS conflit
    FROM chambres c
    JOIN hotels h ON c.code_hotel = h.code_hotel
    LEFT JOIN reservations r 
        ON r.code_chambre = c.code_chambre 
       AND r.statut_reservation IN ('occupé','réservé')
       AND r.date_debut_entre < :date_depart
       AND r.date_fin_sortie   > :date_arrivee
    WHERE h.etat_hotel = 'actif'
      AND c.etat_chambre != 'occupée'
";

$params = [];
if($dest!==''){
    $sql .= " AND (h.ville_hotel LIKE :dest OR h.nom_hotel LIKE :dest OR h.quartier_hotel LIKE :dest)";
    $params[':dest'] = "%$dest%";
}
if($in!=='' && $out!==''){
    $params[':date_arrivee'] = $in;
    $params[':date_depart']  = $out;
}else{
    // si pas de dates on ignore la vérif de conflit
    $sql = str_replace('AND r.date_debut_entre < :date_depart','',$sql);
    $sql = str_replace('AND r.date_fin_sortie   > :date_arrivee','',$sql);
}
$sql .= " ORDER BY c.prix_chambre DESC";

$stmt = $pdo->prepare($sql);
foreach($params as $k=>$v) $stmt->bindValue($k,$v);
$stmt->execute();
$chambres = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------- RÉSERVATION ----------
$message = '';
if(isset($_POST['action']) && $_POST['action']==='reserver'){
    $code_chambre = $_POST['code_chambre'];
    $code_client  = $_POST['code_client'];
    $date_arrivee = $_POST['date_arrivee'];
    $date_depart  = $_POST['date_depart'];
    $adultes      = (int)$_POST['adultes'];
    $enfants      = (int)$_POST['enfants'];

    // Vérif dates
    if($date_arrivee >= $date_depart){
        $message = "<div class='alert alert-danger'>La date de départ doit être postérieure à la date d'arrivée.</div>";
    }else{
        // Vérif disponibilité (même logique que la recherche)
        $check = $pdo->prepare("
            SELECT COUNT(*) FROM reservations
            WHERE code_chambre = ?
              AND statut_reservation IN ('occupé','réservé')
              AND date_debut_entre < ?
              AND date_fin_sortie   > ?
        ");
        $check->execute([$code_chambre,$date_depart,$date_arrivee]);
        if($check->fetchColumn()>0){
            $message = "<div class='alert alert-danger'>Cette chambre n'est pas disponible pour ces dates.</div>";
        }else{
            // Prix & calcul
            $prixStmt = $pdo->prepare("SELECT prix_chambre FROM chambres WHERE code_chambre = ?");
            $prixStmt->execute([$code_chambre]);
            $prix = (float)$prixStmt->fetchColumn();

            $d1 = new DateTime($date_arrivee);
            $d2 = new DateTime($date_depart);
            $duree = $d1->diff($d2)->days;
            $ht    = $prix * $duree;
            $tva   = $ht * 0.1925;
            $ttc   = $ht + $tva;

            // Codes uniques
            $cntRes = $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn() + 1;
            $cntFac = $pdo->query("SELECT COUNT(*) FROM factures")->fetchColumn() + 1;
            $numRes = 'RES'.str_pad($cntRes,3,'0',STR_PAD_LEFT);
            $codeFac= 'FAC'.str_pad($cntFac,3,'0',STR_PAD_LEFT);

            try{
                $pdo->beginTransaction();

                // Facture
                $pdo->prepare("INSERT INTO factures 
                    (code_facture,titre_facture,date_facture,montant_ht,taux_taxes,type_taxes,montant_ttc,etat_facture,code_client)
                    VALUES (?,?,CURDATE(),?,'19.25','TVA',?,'en attente',?)")
                ->execute([$codeFac,"Réservation $code_chambre",$ht,$ttc,$code_client]);

                // Réservation
                $pdo->prepare("
                    INSERT INTO reservations 
                    (numero_reservation,date_reservation,heure_reservation,
                     date_debut_entre,date_fin_entre,date_debut_sortie,date_fin_sortie,
                     type_reservation,code_chambre,code_client,code_facture,
                     duree_jours,prix_chambre,montant_reservation,
                     statut_reservation,observation_reservation,etat_reservation)
                    VALUES (?,?,CURTIME(),?,?,?,?, 'Nuitée',?,?,?, ?,?,?,'réservé','Réservation via site','en cours')
                ")->execute([
                    $numRes,$date_arrivee,$date_arrivee,$date_depart,$date_depart,
                    $code_chambre,$code_client,$codeFac,
                    $duree,$prix,$ht
                ]);

                // MAJ chambre
                $pdo->prepare("UPDATE chambres SET etat_chambre='réservée' WHERE code_chambre=?")
                    ->execute([$code_chambre]);

                $pdo->commit();
                $message = "<div class='alert alert-success'>Réservation réussie ! <strong>$numRes</strong></div>";
            }catch(Exception $e){
                $pdo->rollBack();
                $message = "<div class='alert alert-danger'>Erreur : ".htmlspecialchars($e->getMessage())."</div>";
            }
        }
    }
}

// Clients pour le formulaire
$clients = $pdo->query("SELECT code_client, nom_prenom_client FROM clients WHERE etat_client='actif'")->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- ==================== NAVBAR ==================== -->
<nav class="navbar navbar-expand-lg navbar-custom fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Soutra CI</a>
        <div class="d-flex align-items-center">
            <span class="text-white me-3">XOF</span>
            <span class="text-white me-3"><i class="fas fa-flag"></i> CI</span>
            <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/inscription" class="btn btn-signup ms-2">S'inscrire</a>
            <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/connexion" class="btn btn-login ms-2">Se connecter</a>
        </div>
    </div>
</nav>

<div class="bg-white border-bottom shadow-sm" style="margin-top:76px;">
    <div class="container">
        <ul class="nav justify-content-center py-3">
            <li class="nav-item"><a class="nav-link nav-link-custom active" href="#"><i class="fas fa-bed"></i> Séjours</a></li>
            <li class="nav-item"><a class="nav-link nav-link-custom" href="#"><i class="fas fa-plane"></i> Vols</a></li>
            <li class="nav-item"><a class="nav-link nav-link-custom" href="#"><i class="fas fa-car"></i> Location</a></li>
        </ul>
    </div>
</div>

<!-- ==================== HERO + RECHERCHE ==================== -->
<section class="hero-section">
    <div class="container">
        <h1>Trouvez votre hébergement idéal en Côte d'Ivoire</h1>
        <p>Plus de 50 hôtels, chambres d'hôtes et résidences disponibles</p>

        <form class="search-form" method="GET">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label-custom"><i class="fas fa-map-marker-alt"></i> Destination</label>
                    <input type="text" class="form-control form-control-custom" name="destination" placeholder="Ville, hôtel..." value="<?=htmlspecialchars($dest)?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label-custom"><i class="fas fa-calendar-check"></i> Arrivée</label>
                    <input type="date" class="form-control form-control-custom" name="date_arrivee" value="<?=htmlspecialchars($in)?>" min="<?=date('Y-m-d')?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label-custom"><i class="fas fa-calendar-times"></i> Départ</label>
                    <input type="date" class="form-control form-control-custom" name="date_depart" value="<?=htmlspecialchars($out)?>" min="<?=date('Y-m-d',strtotime('+1 day'))?>">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-search w-100"><i class="fas fa-search"></i> Rechercher</button>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- ==================== RÉSULTATS ==================== -->
<!-- ==================== RÉSULTATS AVEC CAROUSEL ==================== -->
<section class="container my-5">
    <?php if($message) echo $message; ?>
    <h2 class="section-title">
        <?= $dest ? 'Résultats pour "'.htmlspecialchars($dest).'"' : 'Toutes les chambres disponibles' ?>
    </h2>

    <?php
    // ---------- RÉCUPÉRATION DES PHOTOS PAR CHAMBRE ----------
    $photoStmt = $pdo->prepare("
        SELECT photo_album, type_photo, titre_album 
        FROM albums 
        WHERE code_chambre = ? AND etat_album = 'actif' 
        ORDER BY code_album
    ");
    $photos_chambres = [];
    foreach($chambres as $c){
        $photoStmt->execute([$c['code_chambre']]);
        $photos = $photoStmt->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($photos)) {
            $photos_chambres[$c['code_chambre']] = array_map(function($p){
                return [
                    'src' => 'data:'.$p['type_photo'].';base64,'.base64_encode($p['photo_album']),
                    'alt' => htmlspecialchars($p['titre_album'] ?? 'Photo chambre')
                ];
            }, $photos);
        } else {
            $photos_chambres[$c['code_chambre']] = [[
                'src' => 'https://via.placeholder.com/500x300/cccccc/999999?text=Aucune+photo',
                'alt' => 'Aucune photo disponible'
            ]];
        }
    }
    ?>

    <div class="row">
        <?php if(empty($chambres)): ?>
            <div class="col-12">
                <div class="no-results">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <p>Aucune chambre disponible pour vos critères.</p>
                    <a href="?" class="btn btn-primary">Réinitialiser</a>
                </div>
            </div>
        <?php else: ?>
            <?php foreach($chambres as $c):
                $dispo = ($c['conflit']===null && $c['etat_chambre']!=='occupée');
                $badge = $dispo ? 'status-available' : 'status-occupied';
                $txt = $dispo ? 'Disponible' : 'Indisponible';
                $photos = $photos_chambres[$c['code_chambre']];
            ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="hotel-card">
                        <!-- CAROUSEL -->
                        <div id="carousel<?= $c['code_chambre'] ?>" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-indicators">
                                <?php foreach($photos as $i => $photo): ?>
                                    <button type="button" data-bs-target="#carousel<?= $c['code_chambre'] ?>" 
                                            data-bs-slide-to="<?= $i ?>" 
                                            class="<?= $i === 0 ? 'active' : '' ?>" 
                                            aria-current="<?= $i === 0 ? 'true' : 'false' ?>" 
                                            aria-label="Slide <?= $i + 1 ?>"></button>
                                <?php endforeach; ?>
                            </div>
                            <div class="carousel-inner">
                                <?php foreach($photos as $i => $photo): ?>
                                    <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                                        <img src="<?= $photo['src'] ?>" 
                                             class="d-block w-100 hotel-img" 
                                             alt="<?= $photo['alt'] ?>"
                                             style="height:220px; object-fit:cover;"
                                             onerror="this.src='https://via.placeholder.com/500x300/cccccc/999999?text=Image+indisponible';">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <!-- Contrôles -->
                            <?php if(count($photos) > 1): ?>
                                <button class="carousel-control-prev" type="button" data-bs-target="#carousel<?= $c['code_chambre'] ?>" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Précédent</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carousel<?= $c['code_chambre'] ?>" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Suivant</span>
                                </button>
                            <?php endif; ?>
                        </div>

                        <!-- INFOS -->
                        <div class="hotel-info">
                            <h5 class="hotel-name"><?=htmlspecialchars($c['nom_hotel'])?></h5>
                            <p class="hotel-location"><i class="fas fa-map-marker-alt"></i>
                                <?=htmlspecialchars($c['ville_hotel']).', '.$c['quartier_hotel']?>
                            </p>
                            <span class="room-type-badge">
                                <?=ucfirst(str_replace('chambre ','',$c['type_chambre']))?>
                            </span>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    <div class="price">
                                        <span style="font-size:0.8rem;">XOF</span>
                                        <?=number_format($c['prix_chambre'],0,',',' ')?>
                                        <small>/ nuit</small>
                                    </div>
                                    <span class="status-badge <?=$badge?>"><i class="fas fa-circle"></i> <?=$txt?></span>
                                </div>
                                <?php if($dispo): ?>
                                    <button class="btn btn-reserve" data-bs-toggle="modal"
                                            data-bs-target="#modal<?= $c['code_chambre'] ?>">Réserver</button>
                                <?php else: ?>
                                    <button class="btn btn-secondary" disabled>Complet</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MODAL RÉSERVATION (inchangée) -->
                <div class="modal fade" id="modal<?= $c['code_chambre'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <form method="POST">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title">Réserver : <?=htmlspecialchars($c['nom_chambre'])?></h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="action" value="reserver">
                                    <input type="hidden" name="code_chambre" value="<?= $c['code_chambre'] ?>">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Client</label>
                                            <select class="form-select" name="code_client" required>
                                                <option value="">Sélectionner</option>
                                                <?php foreach($clients as $cl): ?>
                                                    <option value="<?= $cl['code_client'] ?>"><?=htmlspecialchars($cl['nom_prenom_client'])?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Arrivée</label>
                                            <input type="date" class="form-control" name="date_arrivee" required min="<?=date('Y-m-d')?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Départ</label>
                                            <input type="date" class="form-control" name="date_depart" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Adultes</label>
                                            <input type="number" class="form-control" name="adultes" value="2" min="1" max="4">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Enfants</label>
                                            <input type="number" class="form-control" name="enfants" value="0" min="0" max="3">
                                        </div>
                                    </div>
                                    <div class="mt-3 p-3 bg-light rounded">
                                        <strong>Prix / nuit :</strong> XOF <?=number_format($c['prix_chambre'],0,',',' ')?><br>
                                        <em>TVA 19,25 % incluse dans le total.</em>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                    <button type="submit" class="btn btn-primary">Confirmer</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- ==================== FOOTER ==================== -->
<footer>
    <div class="container">
        <div class="row">
            <div class="col-md-3"><h5 class="fw-bold mb-3">Soutra CI</h5><p class="text-muted small">Plateforme n°1 de réservation en Côte d'Ivoire.</p></div>
            <div class="col-md-3"><h5 class="fw-bold mb-3">Liens rapides</h5>
                <a href="#" class="footer-link">Hôtels Abidjan</a>
                <a href="#" class="footer-link">Promotions</a>
            </div>
            <div class="col-md-3"><h5 class="fw-bold mb-3">Support</h5>
                <a href="#" class="footer-link">Aide</a>
                <a href="#" class="footer-link">Contact</a>
            </div>
            <div class="col-md-3"><h5 class="fw-bold mb-3">Légal</h5>
                <a href="#" class="footer-link">Confidentialité</a>
                <a href="#" class="footer-link">CGU</a>
            </div>
        </div>
        <hr>
        <div class="text-center text-muted small">© 2025 Soutra CI – Tous droits réservés.</div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Validation simple côté client
    document.querySelectorAll('input[name="date_depart"]').forEach(d=>d.addEventListener('change',function(){
        const arr = document.querySelector('input[name="date_arrivee"]');
        if(arr.value && this.value && arr.value >= this.value){
            alert('La date de départ doit être postérieure à la date d\'arrivée.');
            this.value='';
        }
    }));
</script>
</body>
</html>