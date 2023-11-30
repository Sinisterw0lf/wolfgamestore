<?php
session_start();
include 'header.php';

if (!isset($_SESSION['id'])) {
    header("Location: connexion.php");
    exit();
}

$role = $_SESSION['role'];

if ($role !== 'admin') {
    // Logique du panier pour les utilisateurs normaux
    echo "Contenu du panier :";
} else {
    // Logique du panier pour les administrateurs
    echo "Contenu du panier administrateur :";
}

// Supprimer un produit du panier
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['supprimer_produit'])) {
    $produit_id = $_POST['produit_id'];
    unset($_SESSION['panier'][$produit_id]);
    echo "Produit supprimé du panier.";
}

// Mettre à jour la quantité d'un produit dans le panier
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modifier_quantite'])) {
    $produit_id = $_POST['produit_id'];
    $nouvelle_quantite = $_POST['nouvelle_quantite'];

    // Vérifier si la nouvelle quantité est valide (supérieure à zéro)
    if ($nouvelle_quantite > 0) {
        $_SESSION['panier'][$produit_id] = $nouvelle_quantite;
        echo "Quantité mise à jour.";
    } else {
        echo "La quantité doit être supérieure à zéro.";
    }
}

// Afficher le contenu du panier
if (!empty($_SESSION['panier'])) {
    echo "<table border='1'>";
    echo "<tr><th>Produit ID</th><th>Quantité</th><th>Prix unitaire</th><th>Total</th><th>Action</th></tr>";
    $total_commande = 0; // Variable pour stocker le total de la commande
    foreach ($_SESSION['panier'] as $produit_id => $details_produit) {
        $quantite = $details_produit['quantite'];
        $prix_unitaire = $details_produit['prix'];
        $total_produit = $quantite * $prix_unitaire;
        $total_commande += $total_produit; // Ajouter au total de la commande

        echo "<tr>";
        echo "<td>$produit_id</td>";
        echo "<td>$quantite</td>";
        echo "<td>$prix_unitaire €</td>"; // Modifier pour utiliser la devise appropriée
        echo "<td>$total_produit €</td>"; // Modifier pour utiliser la devise appropriée
        echo "<td>
                <form method='post' action=''>
                    <input type='hidden' name='produit_id' value='$produit_id'>
                    <input type='number' name='nouvelle_quantite' value='$quantite'>
                    <input type='submit' name='modifier_quantite' value='Modifier'>
                </form>
                <form method='post' action=''>
                    <input type='hidden' name='produit_id' value='$produit_id'>
                    <input type='submit' name='supprimer_produit' value='Supprimer'>
                </form>
              </td>";
        echo "</tr>";
    }
    echo "<tr><td colspan='3'>Total de la commande :</td><td>$total_commande €</td></tr>";
    echo "</table>";
} else {
    echo "Le panier est vide.";
}

// Formulaire pour ajouter les informations de l'utilisateur
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['valider_panier'])) {
    $utilisateur_id = $_SESSION['id'];
    $nom = $_POST['nom'];
    $adresse = $_POST['adresse'];
    $code_postal = $_POST['code_postal'];
    $ville = $_POST['ville'];

    // Insérer les informations de la commande dans la table "commandes"
    $stmt_commande = $conn->prepare("INSERT INTO commandes (utilisateur_id, nom, adresse, code_postal, ville, date_commande) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt_commande->bind_param("issss", $utilisateur_id, $nom, $adresse, $code_postal, $ville);

    if ($stmt_commande->execute()) {
        echo "Commande validée avec succès!";
        $commande_id = $stmt_commande->insert_id;

        // Insérer les détails de la commande dans la table "details_commande"
        foreach ($_SESSION['panier'] as $produit_id => $quantite) {
            $stmt_details_commande = $conn->prepare("INSERT INTO details_commande (commande_id, produit_id, quantite) VALUES (?, ?, ?)");
            $stmt_details_commande->bind_param("iii", $commande_id, $produit_id, $quantite);
            $stmt_details_commande->execute();
            $stmt_details_commande->close();
        }

        // Vider le panier
        $_SESSION['panier'] = [];
    } else {
        echo "Erreur lors de la validation de la commande: " . $stmt_commande->error;
    }

    $stmt_commande->close();
}
?>

<!-- Formulaire pour ajouter les informations de l'utilisateur -->
<form method="post" action="">
    <label for="nom">Nom :</label>
    <input type="text" name="nom" required><br>

    <label for="adresse">Adresse :</label>
    <input type="text" name="adresse" required><br>

    <label for="code_postal">Code postal :</label>
    <input type="text" name="code_postal" required><br>

    <label for="ville">Ville :</label>
    <input type="text" name="ville" required><br>

    <input type="submit" name="valider_panier" value="Valider le panier">
</form>