<?php 
try{
$pdo = new PDO('mysql:host=localhost;dbname=u738064605_soutra','root','');
} catch(Exception $e)
{
	echo 'Exception reçue : ',  $e->getMessage();
}



 ?>