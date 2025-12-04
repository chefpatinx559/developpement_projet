<?php
session_start();
class Utilisateur
{

    public function dashboard() {
 
        /* Protection */
        /*if (!isset($_SESSION['login']) and ! isset($_SESSION['mdp'])) {
            ?>
            <script type='text/javascript'>document.location.replace('<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/deconnexion');</script>";
            <?php
        }
        if(!empty($_SESSION['role'])){ 
            if ($_SESSION['role'] != 'Superviseur' and $_SESSION['role'] != 'Administrateur') { 
                session_destroy();
                ?>
                <script type='text/javascript'>document.location.replace('<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/deconnexion');</script>";
                <?php
            }
        }
*/
        include "views/utilisateurs/tableau-bord.php";
    }
    public function enregistrement() {
 
        /* Protection */
        /*if (!isset($_SESSION['login']) and ! isset($_SESSION['mdp'])) {
            ?>
            <script type='text/javascript'>document.location.replace('<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/deconnexion');</script>";
            <?php
        }
        if(!empty($_SESSION['role'])){ 
            if ($_SESSION['role'] != 'Superviseur' and $_SESSION['role'] != 'Administrateur') { 
                session_destroy();
                ?>
                <script type='text/javascript'>document.location.replace('<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/deconnexion');</script>";
                <?php
            }
        }
*/
        include "views/utilisateurs/enregistrement-utilisateur.php";
    }

    public function liste() {
 
        /* Protection
        if (!isset($_SESSION['login']) and ! isset($_SESSION['mdp'])) {
            ?>
            <script type='text/javascript'>document.location.replace('<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/deconnexion');</script>";
            <?php
        }
        if(!empty($_SESSION['role'])){ 
            if ($_SESSION['role'] != 'Superviseur' and $_SESSION['role'] != 'Administrateur') { 
                session_destroy();
                ?>
                <script type='text/javascript'>document.location.replace('<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/deconnexion');</script>";
                <?php
            }
        } */

       include "views/utilisateurs/liste-utilisateur.php";
    }

    public function connexion()
{
    /*if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $login = trim($_POST['login']);
        $mdp = $_POST['mdp'];

        if (empty($login) || empty($mdp)) {
            $_SESSION['message'] = "Erreur : Tous les champs sont obligatoires.";
            header("Location: ?action=connexion");
            exit;
        }

        try {
            global $pdo;
            $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE login = ? AND etat = 'actif' AND mdp=?");
            $stmt->execute([$login]);
            $user = $stmt->fetch();

            if ($user && password_verify($mdp, $user['mdp'])) {
                // === SESSION (identique à ton CRUD) ===
                $_SESSION['utilisateur_id'] = $user['utilisateur_id'];
                $_SESSION['nom_prenom'] = $user['nom_prenom'];
                $_SESSION['login'] = $user['login'];
                $_SESSION['mdp'] = $user['mdp']; // pour ta protection
                $_SESSION['role'] = $user['role'];
                $_SESSION['photo'] = $user['photo'] 
                    ? 'data:' . $user['type_photo'] . ';base64,' . base64_encode($user['photo']) 
                    : "https://via.placeholder.com/160x160/6c757d/ffffff?text=" . substr($user['nom_prenom'], 0, 2);

                $_SESSION['message'] = "Connexion réussie !";
                header("Location: ");
                exit;
            } else {
                $_SESSION['message'] = "Erreur : Login ou mot de passe incorrect.";
                header("Location: ?action=connexion");
                exit;
            }
        } catch (Exception $e) {
            $_SESSION['message'] = "Erreur : " . $e->getMessage();
            header("Location: ?action=connexion");
            exit;
        }
    }
    */
    include'views/utilisateurs/connexion-utilisateur.php';
}
public function inscription()
{
include"views/utilisateurs/inscription-utilisateur.php";
 }  



 public function profil()
{
include"views/utilisateurs/profil.php";
 }  


 public function deconnexion()
{
session_destroy();
?>
            <script type='text/javascript'>document.location.replace('<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/connexion');</script>";
            <?php
 }   
}
?>