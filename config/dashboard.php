<<<<<<< HEAD
<style>
    .user-menu .dropdown-menu {
    z-index: 1070;
}
</style>
<!-- ==================== NAVBAR ==================== -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                <i class="fas fa-bars"></i>
            </a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="index.php" class="nav-link">Accueil</a>
=======
<?php
// config/dashboard.php
$user_name = "Jean Dupont";
$user_role = "Administrateur";
$user_photo = "https://via.placeholder.com/160x160/007bff/ffffff?text=JD";

function old_url($path = '') {
    $base = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== 'off' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER["PHP_SELF"]), '/');
    return $base . $path;
}
?>

<!-- ==================== NAVBAR (inchangée - déjà parfaite) ==================== -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light border-bottom-0 shadow-sm">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="index.php" class="nav-link fw-semibold">Accueil</a>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
        </li>
    </ul>

    <ul class="navbar-nav ml-auto">
<<<<<<< HEAD
        <li class="nav-item dropdown user-menu">
            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                <img src="data:<?php echo $_SESSION['type_photo']; ?>;base64,<?php echo base64_encode($_SESSION['photo']); ?>" class="user-image img-circle elevation-2" alt="User Image">
                <span class="d-none d-md-inline"><?= $_SESSION['nom_prenom']; ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-start">
                <li class="user-header bg-primary">
                    <img src="data:<?php echo $_SESSION['type_photo']; ?>;base64,<?php echo base64_encode($_SESSION['photo']); ?>" class="img-circle elevation-2" alt="User Image">
                    <p>
                        <?= $_SESSION['nom_prenom'] ?> - <?= $_SESSION['role'] ?>
                    </p>
                </li>
                <li class="user-body">
                    <div class="row">
                        <div class="col-6 text-center">
                            <a href="#" class="btn btn-default btn-flat">Profil</a>
                        </div>
                        <div class="col-6 text-center">
                            <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/deconnexion" class="btn btn-default btn-flat">Déconnexion</a>
                        </div>
                    </div>
                </li>
            </ul>
        </li>
    </ul>
=======
    <li class="nav-item dropdown user-menu">
        <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown">
            <img src="data:<?php echo $_SESSION['type_photo'] ?? 'image/jpeg'; ?>;base64,<?php echo base64_encode($_SESSION['photo'] ?? ''); ?>"
                 class="user-image img-circle elevation-2 border border-white"
                 alt="User Image"
                 style="width: 38px; height: 38px; object-fit: cover;">
            <span class="d-none d-md-inline ms-2 fw-semibold"><?= htmlspecialchars($_SESSION['nom_prenom'] ?? 'Utilisateur'); ?></span>
            <i class="fas fa-chevron-down ms-1 text-muted small d-none d-lg-inline"></i>
        </a>

        <!-- Dropdown menu AdminLTE amélioré -->
        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end shadow-lg border-0 mt-2" style="border-radius: 16px; min-width: 280px;">
            <!-- En-tête avec fond dégradé et photo plus grande -->
            <li class="user-header bg-primary position-relative overflow-hidden" style="border-radius: 16px 16px 0 0; padding: 2.5rem 1.5rem;">
                <div class="text-center">
                    <div class="position-relative d-inline-block">
                        <img src="data:<?php echo $_SESSION['type_photo'] ?? 'image/jpeg'; ?>;base64,<?php echo base64_encode($_SESSION['photo'] ?? ''); ?>"
                             class="img-circle elevation-4 border border-white border-4"
                             alt="User Avatar"
                             style="width: 90px; height: 90px; object-fit: cover;">
                        <!-- Point vert "en ligne" -->
                        <div class="position-absolute bottom-0 end-0 bg-success rounded-circle border border-white" 
                             style="width: 26px; height: 26px; box-shadow: 0 0 0 4px rgba(40,167,69,0.3);"></div>
                    </div>

                    <p class="text-white mt-3 mb-1 fw-bold fs-5">
                        <?= htmlspecialchars($_SESSION['nom_prenom'] ?? 'Utilisateur'); ?>
                    </p>
                    <p class="text-white opacity-90 mb-0">
                        <small><?= htmlspecialchars($_SESSION['role'] ?? 'Membre'); ?></small>
                    </p>
                </div>
            </li>

            <!-- Corps du menu -->
            <li class="user-body bg-white" style="border-radius: 0 0 16px 16px;">
                <div class="p-3">
                    <a href="#" class="d-block text-decoration-none py-3 px-3 rounded hover-bg-light text-dark">
                        <i class="fas fa-user-circle text-primary me-3"></i> Mon Profil
                    </a>
                    <a href="#" class="d-block text-decoration-none py-3 px-3 rounded hover-bg-light text-dark">
                        <i class="fas fa-cog text-muted me-3"></i> Paramètres
                    </a>
                    <hr class="my-2">

                    <!-- Bouton Déconnexion en rouge -->
                    <a href="<?= old_url('/utilisateur/deconnexion') ?>" 
                       class="btn btn-danger btn-block text-white fw-bold mt-2">
                        <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                    </a>
                </div>

                <!-- Pied de page avec date de connexion -->
                <div class="text-center py-2 bg-light small text-muted border-top">
                    Connecté le <?= date('d/m/Y à H:i') ?>
                </div>
            </li>
        </ul>
    </li>
</ul>
    


>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
</nav>

<!-- ==================== SIDEBAR ==================== -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="index.php" class="brand-link text-center py-3">
        <span class="brand-text font-weight-light fw-bold">Soutra+</span>
    </a>

    <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="data:<?php echo $_SESSION['type_photo']; ?>;base64,<?php echo base64_encode($_SESSION['photo']); ?>" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block text-white"><?= $_SESSION['nom_prenom'] ?></a>
                <span class="badge badge-success"><?= $_SESSION['role'] ?></span>
            </div>
        </div>

        <!-- ==================== MENU PRINCIPAL ==================== -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                <!-- TABLEAU DE BORD -->
                <li class="nav-item">
                    <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/dashboard" class="nav-link">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Tableau de bord</p>
                    </a>
                </li>

                <!-- CHAMBRES -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-bed"></i>
                        <p>Chambres <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/chambre/enregistrement" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Enregistrement Chambres</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/chambre/liste" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Liste des Chambres</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- RÉSERVATIONS -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-calendar-check"></i>
                        <p>Réservations <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/reservation/enregistrement" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Enregistrement Réservations</p>
                            </a>
                        </li>
<<<<<<< HEAD
                        <li class="nav-item">
                            <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/reservation/reservation_par_hotel" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Par hôtel (période)</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/reservation/reservation_par_chambre" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Par chambre (période)</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/reservation/chambre_occupee_periode" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Chambres occupées (période)</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href=<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/reservation/chambre_occupee_periode" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Chambres réservées (période)</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/reservation/liste_chambre_libre_periode" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Chambres libres (période)</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/reservation/liste_reservation_facture" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Par facture</p>
=======


                        <li class="nav-item">
                            <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/reservation/reservation_par_hotel" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Details reservations</p>
                            </a>
                        </li>


 
                        <li class="nav-item">
                            <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/reservation/chambre_occupee_hotel" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Etats des chambres</p>
                            </a>
                        </li>



          
                        <li class="nav-item">
                            <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/reservation/liste_chambre_libre_hotel" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Listes par type</p>
                            </a>
                        </li>


                       
                        <li class="nav-item">
                            <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/reservation/liste_reservation_facture" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Factures reservations</p>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- CLIENTS -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-user"></i>
                        <p>Clients <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/clients/enregistrement" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Enregistrement Clients</p>
                            </a>
                        </li>
<<<<<<< HEAD
=======


                        <li class="nav-item">
                            <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/clients/liste" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Liste Clients</p>
                            </a>
                        </li>



>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                    </ul>
                </li>

                <!-- DOCUMENTS -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-file-alt"></i>
                        <p>Documents <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/document/enregistrement" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Enregistrement Documents</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- FACTURES -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-file-invoice-dollar"></i>
                        <p>Factures <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/facture/enregistrement" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Enregistrement Factures</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- UTILISATEURS -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Utilisateurs <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/enregistrement" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Liste utilisateurs</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- TRANSACTIONS -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-exchange-alt"></i>
                        <p>Transactions <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/transaction/enregistrement" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Enregistrement Transactions</p>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/transaction/transaction_facture" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
<<<<<<< HEAD
                                <p>Par facture (période)</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/transaction/transaction_type" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Par type (période)</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/transaction/impression" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Impression reçu</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/transaction/rapport" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Reporting</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/transaction/solde_caisse" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Solde caisse</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/transaction/solde_client" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Solde clients</p>
                            </a>
                        </li>
=======
                                <p>Transactions globales</p>
                            </a>
                        </li>


                        <li class="nav-item">
                            <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/transaction/solde_caisse" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Soldes</p>
                            </a>
                        </li>
                       
                        
                        <li class="nav-item">
                            <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/transaction/rapport" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Rapports transactions</p>
                            </a>
                        </li>
                        
                       
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                    </ul>
                </li>

                <!-- ALBUMS -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-images"></i>
                        <p>Albums <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/album/enregistrement" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Enregistrement Albums</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- HÔTELS -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-hotel"></i>
                        <p>Hôtels <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/hotel/enregistrement" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Enregistrement Hôtels</p>
                            </a>
                        </li>
<<<<<<< HEAD
=======

                        <li class="nav-item">
                            <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/hotel/liste" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Liste Hôtels</p>
                            </a>
                        </li>



>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                    </ul>
                </li>

                <!-- PARAMÈTRES -->
                <li class="nav-header text-uppercase">Paramètres</li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-cog"></i>
                        <p>Configuration</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>