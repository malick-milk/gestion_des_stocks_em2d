<?php
require_once "../includes/session.php";
//on detruit toute la session
session_destroy();
//retour a la page de connexion
header("Location: connexion.php");
exit;
