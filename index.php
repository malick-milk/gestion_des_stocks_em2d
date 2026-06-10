<?php
//liste de toute les commandes
require_once "../includes/session.php";
require_once "../configuration/database.php";
require_once "../includes/header.php";
$sql="SELECT * FROM commandes WHERE id_user=:id_user ORDER BY date_commande DESC";
$stmt=$connexion->prepare($sql);
$stmt->execute([':id_user'=>$_SESSION['id_user']]);
$commandes=$stmt->fetchAll();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 style="color: var(--deep-coffee);">
        <i class="bi bi-cart"></i> Mes Commandes
    </h3>
    <a href="ajouter.php" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Nouvelle commande
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <?php
    $messages = [
        'ajoute'  => ['type' => 'success', 'texte' => 'Commande ajoutée avec succès !'],
        'livrer'   => ['type' => 'success', 'texte' => 'Commande marquée comme livrée !'],
        'annuler'  => ['type' => 'warning', 'texte' => 'Commande annulée.'],
    ];
    $msg = $messages[$_GET['msg']] ?? null;
    ?>
    <?php if ($msg): ?>
        <div class="alert alert-<?php echo $msg['type'] ?>"><?php echo $msg['texte'] ?></div>
    <?php endif; ?>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        Liste des commandes (<?php echo count($commandes) ?>)
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Client</th>
                    <th>Adresse</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($commandes)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4">Aucune commande.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($commandes as $cmd): ?>
                        <tr>
                            <td>#<?php echo $cmd['id_commande'] ?></td>
                            <td><?php echo htmlspecialchars($cmd['nom_client']) ?></td>
                            <td><?php echo htmlspecialchars($cmd['adresse_client']) ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($cmd['date_commande'])) ?></td>
                            <td>
                                <?php if ($cmd['statut'] == 'en_attente'): ?>
                                    <span class="badge bg-warning text-dark">En attente</span>
                                <?php elseif ($cmd['statut'] == 'livrer'): ?>
                                    <span class="badge bg-success">Livrer</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Annuler</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="detail.php?id=<?php echo $cmd['id_commande'] ?>"
                                   class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye">Details</i>
                                </a>
                                <?php if ($cmd['statut'] == 'en_attente'): ?>
                                    <a href="statut.php?id=<?php echo $cmd['id_commande'] ?>&statut=livrer"
                                       class="btn btn-sm btn-success"
                                       onclick="return confirm('Confirmer la livraison ?')">
                                        <i class="bi bi-check-lg">Livré</i>
                                    </a>
                                    <a href="statut.php?id=<?php echo $cmd['id_commande'] ?>&statut=annuler"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Annuler cette commande ?')">
                                        <i class="bi bi-x-lg">Annulé</i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once "../includes/footer.php"; ?>