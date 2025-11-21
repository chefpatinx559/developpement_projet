<?php 
try{
$pdo = new PDO('mysql:host=localhost;dbname=app_hotel','root','');
} catch(Exception $e)
{
	echo 'Exception reçue : ',  $e->getMessage();
}



 ?>