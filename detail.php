<?php 
//detaiil d'une commande 
require_once "../includes/session.php";
require_once "../configuration/database.php";
$id=$_GET['id'] ?? null;
if(!$id){
    header("Location: index.php");
    exit();
}
// recuperer la commande 
$sql="SELECT * FROM commandes WHERE id_commande=:id AND id_user=:id_user";
$stmt=$connexion->prepare($sql);
$stmt->execute([
    ':id'=>$id,
    ':id_user'=>$_SESSION['id_user']
]);
$commande=$stmt->fetch();
if(!$commande){
    header("Location: index.php");
    exit();
}
//recuperer les lignes de la commande
$sql="SELECT lc.*,p.nom,p.image FROM ligne_commandes lc JOIN produits p ON lc.id_produit=p.id_produit WHERE lc.id_commande =:id";
$stmt=$connexion->prepare($sql);
$stmt->execute([':id'=>$id]);
$lignes=$stmt->fetchAll();
//calculer le total 
$total = 0;
foreach($lignes as $ligne){
    $total=$ligne['prix_unitaire']+$ligne['quantite'];
}
require_once "../includes/header.php";
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 style="color: var(--deep-coffee);">
        <i class="bi bi-receipt"></i> Détail commande #<?php echo $commande['id_commande'] ?>
    </h4>
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Retour
    </a>
</div>

<!-- Infos client -->
<div class="card mb-4">
    <div class="card-header">Informations client</div>
    <div class="card-body">
        <p><strong>Client :</strong> <?php echo htmlspecialchars($commande['nom_client']) ?></p>
        <p><strong>Adresse :</strong> <?php echo htmlspecialchars($commande['adresse_client']) ?></p>
        <p><strong>Date :</strong> <?php echo date('d/m/Y H:i', strtotime($commande['date_commande'])) ?></p>
        <p><strong>Statut :</strong>
            <?php if ($commande['statut'] == 'en_attente'): ?>
                <span class="badge bg-warning text-dark">En attente</span>
            <?php elseif ($commande['statut'] == 'livrer'): ?>
                <span class="badge bg-success">Livré</span>
            <?php else: ?>
                <span class="badge bg-danger">Annulé</span>
            <?php endif; ?>
        </p>
    </div>
</div>

<!-- Lignes produits -->
<div class="card">
    <div class="card-header">Produits commandés</div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Produit</th>
                    <th>Prix unitaire</th>
                    <th>Quantité</th>
                    <th>Sous-total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lignes as $ligne): ?>
                    <tr>
                        <td>
                            <?php if ($ligne['image']): ?>
                                <img src="../assets/image/<?php echo htmlspecialchars($ligne['image']) ?>"
                                     width="45" height="45" style="object-fit:cover; border-radius:6px;">
                            <?php else: ?>
                                <i class="bi bi-image" style="font-size:1.8rem; color:var(--muted-sand);"></i>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($ligne['nom']) ?></td>
                        <td><?php echo number_format($ligne['prix_unitaire'], 0, ',', ' ') ?> FCFA</td>
                        <td><?php echo $ligne['quantite'] ?></td>
                        <td><?php echo number_format($ligne['prix_unitaire'] * $ligne['quantite'], 0, ',', ' ') ?> FCFA</td>
                    </tr>
                <?php endforeach; ?>
                <tr style="background-color: var(--muted-sand);">
                    <td colspan="4" class="text-end fw-bold">Total</td>
                    <td class="fw-bold"><?php echo number_format($ligne['prix_unitaire'] * $ligne['quantite'], 0, ',', ' ') ?> FCFA</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php require_once "../includes/footer.php"; ?>