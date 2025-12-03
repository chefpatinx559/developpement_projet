<?php 

class album
{

public function enregistrement() {
  

        /* Protection
        if (!isset($_SESSION['login']) and ! isset($_SESSION['mdp'])) {
            ?>
            <script type='text/javascript'>document.location.replace('<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/deconnexion');</script>";
            <?php
        }

if(!empty($_SESSION['role'])){ if ($_SESSION['role'] != 'Superviseur'  and $_SESSION['role'] != 'Administrateur') {                session_destroy();
                ?>
                <script type='text/javascript'>document.location.replace('<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/deconnexion');</script>";
                <?php
            }
        }
        */

        include "views/albums/enregistrement-album.php";
    }



   



public function import() {
  

        /* Protection
        if (!isset($_SESSION['login']) and ! isset($_SESSION['mdp'])) {
            ?>
            <script type='text/javascript'>document.location.replace('<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/deconnexion');</script>";
            <?php
        }

if(!empty($_SESSION['role'])){ if ($_SESSION['role'] != 'Superviseur'  and $_SESSION['role'] != 'Administrateur') {                session_destroy();
                ?>
                <script type='text/javascript'>document.location.replace('<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/deconnexion');</script>";
                <?php
            }
        } */

       include "views/albums/get_chambres.php";
    }


}





 ?>