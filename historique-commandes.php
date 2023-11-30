<?php
session_start();
include 'header.php';
include 'config.php';

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

    // Vérifions si le formulaire a été soumis et affichons un message approprié
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['marquer_envoye'])) {
        echo "La commande a été marquée comme envoyée.";
    }
}

$utilisateur_id = $_SESSION['id'];

// Récupérez l'historique des commandes de l'utilisateur depuis la base de données
$result = $conn->query("SELECT * FROM commandes WHERE utilisateur_id = $utilisateur_id ORDER BY date_commande DESC");

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
?>
        <div class="bg-white p-4 mb-4 rounded-lg shadow-md">
            <p class="text-lg font-bold">Commande ID: <?php echo $row['id']; ?></p>
            <p>Total: <?php echo $row['total']; ?>€ - Date: <?php echo $row['date_commande']; ?></p>

            <!-- Détails des produits commandés -->
            <?php
            $commande_id = $row['id'];
            $details_result = $conn->query("SELECT * FROM details_commande WHERE commande_id = $commande_id");

            if ($details_result->num_rows > 0) {
                echo "<p class='mt-2'>Produits commandés:</p>";
                while ($details_row = $details_result->fetch_assoc()) {
                    $produit_id = $details_row['produit_id'];
                    $quantite = $details_row['quantite'];

                    // Récupérez les détails du produit depuis la table des produits
                    $produit_result = $conn->query("SELECT * FROM produits WHERE id = $produit_id");

                    if ($produit_result->num_rows == 1) {
                        $produit_row = $produit_result->fetch_assoc();
            ?>
                        <p class="ml-4">- Nom: <?php echo $produit_row['nom']; ?> - Quantité: <?php echo $quantite; ?></p>
            <?php
                    }
                }
            } else {
                echo "<p class='mt-2'>Aucun détail de commande trouvé pour cette commande.</p>";
            }

            // Ajoutons un formulaire pour l'administrateur pour marquer la commande comme envoyée
            if ($role === 'admin') {
            ?>
                <form method='post' action='' class='mt-2'>
                    <input type='hidden' name='commande_id' value='<?php echo $row['id']; ?>'>
                    <button type='submit' name='marquer_envoye' class='bg-blue-500 text-white p-2 rounded-md cursor-pointer'>Marquer comme envoyée</button>
                </form>
            <?php
            }

            echo "</div>";
        }
    } else {
        echo "<p class='text-gray-500'>Aucune commande trouvée dans l'historique.</p>";
    }

$conn->close();
?>
