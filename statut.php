<?php
require_once "../includes/session.php";
require_once "../configuration/database.php";

$id     = $_GET['id']     ?? null;
$statut = $_GET['statut'] ?? null;

if (!$id || !in_array($statut, ['livrer', 'annuler'])) {
    header("Location: index.php");
    exit();
}

// Vérifier que la commande appartient à cet utilisateur et est en attente
$sql  = "SELECT * FROM commandes WHERE id_commande = :id AND id_user = :id_user AND statut = 'en_attente'";
$stmt = $connexion->prepare($sql);
$stmt->execute([':id' => $id, ':id_user' => $_SESSION['id_user']]);
$commande = $stmt->fetch();

if (!$commande) {
    header("Location: index.php");
    exit();
}

// Si livré → décrémenter le stock
if ($statut == 'livrer') {
    $sql  = "SELECT * FROM ligne_commandes WHERE id_commande = :id";
    $stmt = $connexion->prepare($sql);
    $stmt->execute([':id' => $id]);
    $lignes = $stmt->fetchAll();

    foreach ($lignes as $ligne) {
        // Récupérer le stock actuel
        $sql_stock  = "SELECT quantite_stock FROM produits WHERE id_produit = :id";
        $stmt_stock = $connexion->prepare($sql_stock);
        $stmt_stock->execute([':id' => $ligne['id_produit']]);
        $stock_actuel = $stmt_stock->fetchColumn();

        // Calculer le nouveau stock
        $nouveau_stock = $stock_actuel - $ligne['quantite'];
        if ($nouveau_stock < 0) $nouveau_stock = 0;

        // Gérer date_rupture
        $date_rupture = null;
        if ($nouveau_stock == 0) {
            $date_rupture = date('Y-m-d H:i:s');
        }

        // Mettre à jour le stock
        $sql_update  = "UPDATE produits
                        SET quantite_stock = :stock, date_rupture = :date_rupture
                        WHERE id_produit = :id";
        $stmt_update = $connexion->prepare($sql_update);
        $stmt_update->execute([
            ':stock'        => $nouveau_stock,
            ':date_rupture' => $date_rupture,
            ':id'           => $ligne['id_produit']
        ]);
    }
}

// Mettre à jour le statut de la commande
$sql  = "UPDATE commandes SET statut = :statut WHERE id_commande = :id";
$stmt = $connexion->prepare($sql);
$stmt->execute([':statut' => $statut, ':id' => $id]);

header("Location: index.php?msg=" . $statut);
exit();
?>