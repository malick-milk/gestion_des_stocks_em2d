<?php 
// Ajouter une commande
require_once "../includes/session.php";
require_once "../configuration/database.php";
$erreurs=[];
//recuperer les produits disponibles pour le formulaire de commande 
$sql="SELECT * FROM produits WHERE id_user=:id_user AND quantite_stock > 0";
$stmt=$connexion->prepare($sql);
$stmt->execute(['id_user'=>$_SESSION['id_user']]);
$produits=$stmt->fetchAll();
if($_SERVER['REQUEST_METHOD']=="POST"){
    $nom_client=trim($_POST['nom_client']);
    $adresse_client=trim($_POST['adresse_client']);
    $ids_produit=$_POST['id_produit'] ?? [];
    $quantites=$_POST['quantite'] ?? [];
    //validation
if(empty($nom_client)) $erreurs[]="Le nom du client est obligatoire";
if(empty($adresse_client)) $erreurs[]="L'adresse client est obligatoire";
if(empty($ids_produit)) $erreurs[]="Ajouter au moins un produit";
if(empty($erreurs)){
    //inserer la commande
    $sql ="INSERT INTO commandes(id_user,nom_client,adresse_client) VALUES (:id_user,:nom,:adresse)";
    $stmt=$connexion->prepare($sql);
    $stmt->execute([
        ':id_user'=>$_SESSION['id_user'],
        ':nom'=>$nom_client,
        ':adresse'=>$adresse_client
        ]);
        //recuperer l'id de la derniere commande inserer
        $id_commande=$connexion->lastInsertId();
        //inserer dans la ligne de commande
        foreach($ids_produit as $index=>$id_produit){
            $qte=(int)$quantites[$index];
            if($qte<=0) continue;//on ignore les lignes vides
            //recuperer le prix actuel du produit
            $sql_prix="SELECT prix_actuel FROM produits WHERE id_produit=:id";
            $stmt_prix=$connexion->prepare($sql_prix);
            $stmt_prix->execute([':id'=>$id_produit]);
            $prix=$stmt_prix->fetchColumn();
            //inserer la ligne 
            $sql_ligne="INSERT INTO ligne_commandes(id_commande, id_produit, quantite, prix_unitaire) VALUES (:commande,:produit,:quantite,:prix)";
            $stmt_ligne=$connexion->prepare($sql_ligne);
            $stmt_ligne->execute([
                ':commande'=>$id_commande,
                ':produit'=>$id_produit,
                ':quantite'=>$qte,
                ':prix'=>$prix
            ]);
        }
        header("Location: index.php?msg=ajouter?avec?succes");
        exit();
}
}
require_once "../includes/header.php";
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 style="color: var(--deep-coffee);">
        <i class="bi bi-cart-plus"></i> Nouvelle commande
    </h4>
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Retour
    </a>
</div>

<div class="card">
    <div class="card-header">Informations de la commande</div>
    <div class="card-body">

        <?php if (!empty($erreurs)): ?>
            <div class="alert alert-danger">
                <?php foreach ($erreurs as $e): ?>
                    <p class="mb-0"><?= $e ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <!-- Infos client -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Nom du client *</label>
                    <input type="text" name="nom_client" class="form-control"
                           value="<?php echo isset($nom_client) ? htmlspecialchars($nom_client) : '' ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Adresse *</label>
                    <input type="text" name="adresse_client" class="form-control"
                           value="<?php echo isset($adresse_client) ? htmlspecialchars($adresse_client) : '' ?>">
                </div>
            </div>

            <!-- Lignes de produits -->
            <h6 style="color: var(--deep-coffee);">Produits commandés</h6>
            <table class="table" id="tableau-produits">
                <thead style="background-color: var(--muted-sand);">
                    <tr>
                        <th>Produit</th>
                        <th>Quantité</th>
                        <th>Prix unitaire</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="lignes-produits">
                    <!-- Ligne par défaut -->
                    <tr>
                        <td>
                            <select name="id_produit[]" class="form-select produit-select">
                                <option value="">-- Choisir --</option>
                                <?php foreach ($produits as $p): ?>
                                    <option value="<?php echo $p['id_produit'] ?>"
                                            data-prix="<?php echo $p['prix_actuel'] ?>">
                                        <?php echo htmlspecialchars($p['nom']) ?>
                                        (<?php echo number_format($p['prix_actuel'], 0, ',', ' ') ?> FCFA)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <input type="number" name="quantite[]"
                                   class="form-control" min="1" value="1">
                        </td>
                        <td class="prix-affiche align-middle">—</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger supprimer-ligne">
                                <i class="bi bi-trash">Supprimer</i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <button type="button" class="btn btn-secondary mb-3" id="ajouter-ligne">
                <i class="bi bi-plus"></i> Ajouter un produit
            </button>

            <br>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> Enregistrer la commande
            </button>
        </form>

    </div>
</div>

<!-- JavaScript : ajout dynamique de lignes -->
<script>
// Modèle d'une ligne produit
function nouvelleLigne() {
    const options = `<?php foreach ($produits as $p): ?>
        <option value="<?php echo $p['id_produit'] ?>" data-prix="<?= $p['prix_actuel'] ?>">
            <?php echo htmlspecialchars($p['nom']) ?>
            (<?php echo number_format($p['prix_actuel'], 0, ',', ' ') ?> FCFA)
        </option>
    <?php endforeach; ?>`;

    return `<tr>
        <td>
            <select name="id_produit[]" class="form-select produit-select">
                <option value="">-- Choisir --</option>
                ${options}
            </select>
        </td>
        <td><input type="number" name="quantite[]" class="form-control" min="1" value="1"></td>
        <td class="prix-affiche align-middle">—</td>
        <td>
            <button type="button" class="btn btn-sm btn-danger supprimer-ligne">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>`;
}

// Ajouter une ligne
document.getElementById('ajouter-ligne').addEventListener('click', function () {
    document.getElementById('lignes-produits').insertAdjacentHTML('beforeend', nouvelleLigne());
    attacherEvenements();
});

// Supprimer une ligne
function attacherEvenements() {
    document.querySelectorAll('.supprimer-ligne').forEach(btn => {
        btn.onclick = function () {
            const lignes = document.querySelectorAll('#lignes-produits tr');
            if (lignes.length > 1) {
                this.closest('tr').remove();
            }
        };
    });

    // Afficher le prix quand on choisit un produit
    document.querySelectorAll('.produit-select').forEach(select => {
        select.onchange = function () {
            const option = this.options[this.selectedIndex];
            const prix   = option.getAttribute('data-prix');
            const cell   = this.closest('tr').querySelector('.prix-affiche');
            cell.textContent = prix
                ? parseInt(prix).toLocaleString('fr-FR') + ' FCFA'
                : '—';
        };
    });
}

attacherEvenements();
</script>

<?php require_once "../includes/footer.php"; ?>