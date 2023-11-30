<?php
session_start();
include 'header.php';
if (!isset($_SESSION['id'])) {
    header("Location: connexion.php");
    exit();
}

$role = $_SESSION['role'];

if ($role === 'admin') {
    header("Location: acces-refuse.php");
    exit();
}

include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['passer_commande'])) {
    // Vérifiez si le panier n'est pas vide
    if (empty($_SESSION['panier'])) {
        echo "Le panier est vide. Ajoutez des produits avant de passer une commande.";
        exit();
    }

    // Calculez le total de la commande
    $total_commande = 0;
    foreach ($_SESSION['panier'] as $produit_id => $quantite) {
        $result = $conn->query("SELECT prix FROM produits WHERE id = $produit_id");
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $total_commande += $row['prix'] * $quantite;
        }
    }

    // Enregistrez la commande dans la base de données
    $utilisateur_id = $_SESSION['id'];

    $stmt = $conn->prepare("INSERT INTO commandes (utilisateur_id, total) VALUES (?, ?)");
    $stmt->bind_param("id", $utilisateur_id, $total_commande);

    if ($stmt->execute()) {
        // Récupérez l'ID de la dernière commande insérée
        $commande_id = $stmt->insert_id;

        // Enregistrez les détails de la commande (produits commandés) dans une autre table (non implémenté dans cet exemple)

        // Videz le panier après le passage de la commande
        unset($_SESSION['panier']);

        echo "Commande passée avec succès! Numéro de commande: " . $commande_id;
    } else {
        echo "Erreur lors du traitement de la commande: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>