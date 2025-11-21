<?php 
try{
<<<<<<< HEAD
$pdo = new PDO('mysql:host=localhost;dbname=soutra','root','');
=======
$pdo = new PDO('mysql:host=localhost;dbname=app_hotel','root','');
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
} catch(Exception $e)
{
	echo 'Exception reçue : ',  $e->getMessage();
}



 ?>