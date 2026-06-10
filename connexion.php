<?php
require_once "../configuration/database.php";
session_start();
$erreurs="";
if($_SERVER['REQUEST_METHOD']=="POST"){
    $email=trim($_POST['email']);
    $mot_de_passe=$_POST['mot_de_passe'];
    $sql= "SELECT * FROM utilisateurs WHERE email=:email";
    $stmt=$connexion->prepare($sql);
    $stmt->execute([':email'=>$email]);
    $user=$stmt->fetch();
    //verifions que l'utilsateur existe
    if($user && password_verify($mot_de_passe,$user['mot_de_passe'])){
        //on stock les informations dans la session
        $_SESSION['id_user']=$user['id_user'];
        $_SESSION['nom']=$user['nom'];
        $_SESSION['nom_boutique']=$user['nom_boutique'];
        header("Location: /gestion_des_stocks/tableau de bord/index.php");
        exit;
    }else{
        $erreur = "email ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Connexion</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="col-md-5">
            <div class="card-shadow">
                <div class="card-body p-4">
                    <h3 class="text-center mb-4">Se connecter</h3>
                    <?php if($erreurs):?>
                        <div class="alert alert-danger"><?php $erreurs ?></div>
                        <?php endif; ?>
                        <form method="POST" action="connexion.php">
                            <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                            <label class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" name="mot_de_passe" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-50">Se connecter</button>
                    </form>
                    <p class="text-align-center mt-3">Pas de compte <a href="registre.php">Creer un compte </a></p>
                    </div>
                    </div>
                    </div>
                    </div>
                    </body>
                    </html>

