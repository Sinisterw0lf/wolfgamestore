<?php
session_start();
include 'header.php';
include 'config.php';

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
    echo "Contenu du panier : ";
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
        $_SESSION['panier'][$produit_id]['quantite'] = $nouvelle_quantite;
        echo "Quantité mise à jour.";
    } else {
        echo "La quantité doit être supérieure à zéro.";
    }
}

// Afficher le contenu du panier
if (!empty($_SESSION['panier'])) {
    echo "<div class='bg-white p-4 rounded-lg shadow-md'>";
    echo "<table class='w-full border-collapse border border-gray-300'>";
    echo "<thead class='bg-gray-200'>";
    echo "<tr><th class='p-2'>Nom</th><th class='p-2'>Image</th><th class='p-2'>Quantité</th><th class='p-2'>Prix unitaire</th><th class='p-2'>Prix total</th><th class='p-2'>Action</th></tr>";
    echo "</thead>";

    $total_prix_panier = 0;

    foreach ($_SESSION['panier'] as $produit_id => $details_produit) {
        $quantite = $details_produit['quantite'];
        $prix_unitaire = $details_produit['prix'];
        $nom_produit = $details_produit['nom'];
        $image_produit = $details_produit['image'];

        // Calculer le prix total pour ce produit
        $prix_total = $quantite * $prix_unitaire;
        $total_prix_panier += $prix_total;

        echo "<tr>";
        echo "<td class='p-2'>$nom_produit</td>";
        echo "<td class='p-2'><img src='uploads/$image_produit' alt='$nom_produit' class='w-12 h-12'></td>";
        echo "<td class='p-2'>$quantite</td>";
        echo "<td class='p-2'>$prix_unitaire €</td>";
        echo "<td class='p-2'>$prix_total €</td>";
        echo "<td class='p-2'>
                <form method='post' action='' class='inline'>
                    <input type='hidden' name='produit_id' value='$produit_id'>
                    <input type='number' name='nouvelle_quantite' value='$quantite' class='border rounded-md p-1'>
                    <button type='submit' name='modifier_quantite' class='text-blue-500'>Modifier</button>
                </form>
                <form method='post' action='' class='inline ml-2'>
                    <input type='hidden' name='produit_id' value='$produit_id'>
                    <button type='submit' name='supprimer_produit' class='text-red-500'>Supprimer</button>
                </form>
              </td>";
        echo "</tr>";
    }

    // Afficher le total des prix
    echo "<tr><td colspan='4' class='p-2 font-bold'>Total</td><td class='p-2 font-bold'>$total_prix_panier €</td><td class='p-2'></td></tr>";

    echo "</table>";

    // Formulaire pour ajouter les informations de l'utilisateur et valider le panier
    ?>
    <form method="post" action="" class="mt-4">
        <input type="hidden" name="panier" value="<?php echo base64_encode(serialize($_SESSION['panier'])); ?>">

        <div class="mb-2">
            <label for="nom" class="block text-sm font-medium text-gray-700">Nom :</label>
            <input type="text" name="nom" required class="mt-1 p-2 w-full border rounded-md">
        </div>

        <div class="mb-2">
            <label for="adresse" class="block text-sm font-medium text-gray-700">Adresse :</label>
            <input type="text" name="adresse" required class="mt-1 p-2 w-full border rounded-md">
        </div>

        <div class="mb-2">
            <label for="code_postal" class="block text-sm font-medium text-gray-700">Code postal :</label>
            <input type="text" name="code_postal" required class="mt-1 p-2 w-full border rounded-md">
        </div>

        <div class="mb-2">
            <label for="ville" class="block text-sm font-medium text-gray-700">Ville :</label>
            <input type="text" name="ville" required class="mt-1 p-2 w-full border rounded-md">
        </div>

        <button type="submit" name="valider_panier" class="bg-blue-500 text-white p-2 rounded-md cursor-pointer">Valider le panier</button>
    </form>
    <?php
    echo "</div>";
} else {
    echo "<p class='text-gray-500'>Le panier est vide.</p>";
}

// Traitement de la validation du panier
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['valider_panier'])) {
    $utilisateur_id = $_SESSION['id'];
    $nom = $_POST['nom'];
    $adresse = $_POST['adresse'];
    $code_postal = $_POST['code_postal'];
    $ville = $_POST['ville'];

    // Calcul du total de la commande
    $total_prix_panier = 0;
    foreach ($_SESSION['panier'] as $produit_id => $details_produit) {
        $quantite = $details_produit['quantite'];
        $prix_unitaire = $details_produit['prix'];
        $total_prix_panier += $quantite * $prix_unitaire;
    }

    // Ajout de la commande à la table `commandes`
    $stmt_commande = $conn->prepare("INSERT INTO commandes (utilisateur_id, nom, adresse, code_postal, ville, total, date_commande) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt_commande->bind_param("issssd", $utilisateur_id, $nom, $adresse, $code_postal, $ville, $total_prix_panier);

    if ($stmt_commande->execute()) {
        $commande_id = $stmt_commande->insert_id;

        // Ajout des détails de la commande à la table `details_commande`
        foreach ($_SESSION['panier'] as $produit_id => $details_produit) {
            $quantite = $details_produit['quantite'];

            $stmt_details_commande = $conn->prepare("INSERT INTO details_commande (commande_id, produit_id, quantite) VALUES (?, ?, ?)");
            $stmt_details_commande->bind_param("iii", $commande_id, $produit_id, $quantite);
            $stmt_details_commande->execute();
            $stmt_details_commande->close();
        }

        // Vider le panier
        $_SESSION['panier'] = [];

        // Redirection vers la page historique-commandes.php
        header("Location: historique-commandes.php");
        exit();
    } else {
        echo "Erreur lors de l'insertion de la commande: " . $stmt_commande->error;
    }

    $stmt_commande->close();
}
?>



