<?php
require_once "../configuration/database.php";
session_start();
$erreurs="";
$succes="";
if($_SERVER['REQUEST_METHOD']=='POST'){
    $nom=trim($_POST['nom']);
    $email=trim($_POST['email']);
    $mot_de_passe=$_POST['mot_de_passe'];
    $nom_boutique=trim($_POST['nom_boutique']);
    //verification
    if(empty($nom)||empty($email)||empty($mot_de_passe)||empty($nom_boutique)){
        $erreur="Tous les champs sont obligatoires.";
    } else{
        //on hashe le mot de passe 
        $hash=password_hash($mot_de_passe, PASSWORD_DEFAULT);
        try{
            $sql="INSERT INTO utilisateurs (nom,email,mot_de_passe,nom_boutique) VALUES (:nom,:email,:mot_de_passe,:nom_boutique)";
            $stmt=$connexion->prepare($sql);
            $stmt->execute([
                ':nom'=>$nom,
                ':email'=>$email,
                ':mot_de_passe'=>$hash,
                ':nom_boutique'=>$nom_boutique
            ]);
            $succes="Compte cree avec succes !<a href='connexion.php'>Se connecter</a>";
        }
        catch(PDOException $e){
            $erreur="Cet email est deja utilise.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Inscription</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="col-md-5">
            <div class="card-shadow">
                <div class="card-body p-4">
                    <h3 class="text-center mb-4">S'inscrire</h3>
                    <?php if($erreurs):?>
                        <div class="alert alert-danger"><?php $erreurs ?></div>
                        <?php endif; ?>
                        <?php if($succes):?>
                            <div class="alert alert-success"><?php $succes ?></div>
                            <?php endif; ?>
                            <form method="POST" action="registre.php">
                                <div class="mb-3">
                                    <label class="form-label">Nom complet</label>
                                    <input type="text" class="form-control" name="nom" required>
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" required>
                                    <label class="form-label">Mot de passe</label>
                                    <input type="password" name="mot_de_passe" class="form-control" required>
                                    <label class="form-label">Nom de la boutique</label>
                                    <input type="text" name="nom_boutique" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-50">S'inscrire</button>
                        </form>
                        <p class="text-center mt-3">Deja un compte <a href="connexion.php">Se connecter</a></p>
                        </div>
                        </div>
                        </div>
                        </div>
                        </div>
                        </body>
                        </html>

