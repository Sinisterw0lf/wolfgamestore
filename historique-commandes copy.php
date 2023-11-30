<?php
session_start();
include 'header.php';
if (!isset($_SESSION['id'])) {
    header("Location: connexion.php");
    exit();
}

$role = $_SESSION['role'];

// Permettez aux administrateurs d'accéder également à l'historique des commandes
if ($role !== 'admin') {
    // Logique de l'historique des commandes pour les utilisateurs normaux
    echo "Historique des commandes pour les utilisateurs normaux...";
} else {
    // Logique de l'historique des commandes pour les administrateurs
    echo "Historique des commandes pour les administrateurs...";

    include 'config.php';

    $utilisateur_id = $_SESSION['id'];

    // Récupérez l'historique des commandes de l'utilisateur depuis la base de données
    $result = $conn->query("SELECT * FROM commandes ORDER BY date_commande DESC");

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "Commande ID: " . $row['id'] . " - Total: $" . $row['total'] . " - Date: " . $row['date_commande'];

            // Vérifiez si la commande a été envoyée (statut = 1)
            if ($row['statut'] == 1) {
                echo " - Statut: Commande envoyée";
            } else {
                // Affichez le bouton pour marquer la commande comme envoyée (accessible uniquement à l'admin)
                echo " - Statut: En attente d'envoi";
                echo "<form method='post' action=''>";
                echo "<input type='hidden' name='commande_id' value='" . $row['id'] . "'>";
                echo "<input type='submit' name='marquer_envoye' value='Marquer comme envoyée'>";
                echo "</form>";
            }

            echo "<br>";

            // Récupérez les détails des produits commandés pour cette commande
            $commande_id = $row['id'];
            $details_result = $conn->query("SELECT * FROM details_commande WHERE commande_id = $commande_id");

            if ($details_result->num_rows > 0) {
                echo "Produits commandés:<br>";
                while ($details_row = $details_result->fetch_assoc()) {
                    $produit_id = $details_row['produit_id'];
                    $quantite = $details_row['quantite'];

                    // Récupérez les détails du produit depuis la table des produits
                    $produit_result = $conn->query("SELECT * FROM produits WHERE id = $produit_id");

                    if ($produit_result->num_rows == 1) {
                        $produit_row = $produit_result->fetch_assoc();
                        echo "- Nom: " . $produit_row['nom'] . " - Quantité: " . $quantite . "<br>";
                    }
                }
            } else {
                echo "Aucun détail de commande trouvé pour cette commande.";
            }

            echo "<hr>";
        }
    } else {
        echo "Aucune commande trouvée dans l'historique.";
    }

    // Traitement pour marquer la commande comme envoyée
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['marquer_envoye'])) {
        $commande_id = $_POST['commande_id'];

        // Mettez à jour le statut de la commande pour indiquer qu'elle a été envoyée
        $stmt_update_commande = $conn->prepare("UPDATE commandes SET statut = 1 WHERE id = ?");
        $stmt_update_commande->bind_param("i", $commande_id);

        if ($stmt_update_commande->execute()) {
            echo "La commande a été marquée comme envoyée avec succès.";
        } else {
            echo "Erreur lors de la mise à jour du statut de la commande: " . $stmt_update_commande->error;
        }

        $stmt_update_commande->close();
    }

    $conn->close();
}
?>