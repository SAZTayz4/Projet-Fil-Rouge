<?php
session_start();

// Détruire toutes les variables de session
$_SESSION = array();

// Détruire la session
session_destroy();

<<<<<<< HEAD
// Rediriger vers la page de connexion avec un chemin absolu
header('Location: /ProjetFileRouge/Backend/auth/login.php');
=======
// Rediriger vers la page de connexion
header('Location: login.php');
>>>>>>> 634ce6e29b4a5095cb42ddfd52b8126da5340ac4
exit();
?> 