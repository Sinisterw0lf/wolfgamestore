<?php
include 'config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $produit_id = $_GET['id'];

    // Récupérer les informations du produit
    $result_produit = $conn->query("SELECT nom, prix, image FROM produits WHERE id = $produit_id");

    if ($result_produit->num_rows == 1) {
        $row_produit = $result_produit->fetch_assoc();

        // Vérifier si l'utilisateur est connecté
        if (isset($_SESSION['id'])) {
            $utilisateur_id = $_SESSION['id'];

            // Vérifier si le produit est déjà dans le panier de l'utilisateur
            $result_panier = $conn->query("SELECT * FROM panier WHERE utilisateur_id = $utilisateur_id AND produit_id = $produit_id");

            if ($result_panier->num_rows > 0) {
                // Si le produit est déjà dans le panier, augmenter la quantité
                $conn->query("UPDATE panier SET quantite = quantite + 1 WHERE utilisateur_id = $utilisateur_id AND produit_id = $produit_id");
            } else {
                // Si le produit n'est pas encore dans le panier, l'ajouter avec une quantité de 1
                $conn->query("INSERT INTO panier (utilisateur_id, produit_id, quantite) VALUES ($utilisateur_id, $produit_id, 1)");
            }

            // Mettez à jour ou créez le tableau $_SESSION['panier']
            if (!isset($_SESSION['panier'])) {
                $_SESSION['panier'] = [];
            }

            // Stockez les détails du produit dans le tableau
            $_SESSION['panier'][$produit_id] = [
                'quantite' => $_SESSION['panier'][$produit_id]['quantite'] ?? 1,
                'prix' => $row_produit['prix'],
                'nom' => $row_produit['nom'],
                'image' => $row_produit['image'],
            ];

            echo "Produit ajouté au panier avec succès!";
            header("Location: index.php");
            exit();
        } else {
            echo "Vous devez être connecté pour ajouter un produit au panier.";
        }
    } else {
        echo "Erreur lors de la récupération des informations du produit.";
    }
} else {
    echo "ID du produit non spécifié.";
}
?>