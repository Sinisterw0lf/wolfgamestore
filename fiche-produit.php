<?php
session_start();
include 'header.php';
include 'config.php';

if (isset($_GET['id'])) {
    $produit_id = $_GET['id'];

    // Récupérer les détails du produit depuis la base de données
    $result = $conn->query("SELECT * FROM produits WHERE id = $produit_id");

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        echo "<div>";
        echo "<img src='uploads/" . $row['image'] . "' alt='" . $row['nom'] . "' width='200'>";
        echo "<h2>" . $row['nom'] . "</h2>";
        echo "<p>" . $row['description_complete'] . "</p>";
        echo "<p>Quantité restante: " . $row['quantite_restante'] . "</p>";
        echo "<p>Prix: $" . $row['prix'] . "</p>";
        echo "</div>";
        echo "<p><a href='ajouter-au-panier.php?id=" . $row['id'] . "'>Ajouter au panier</a></p>";

    } else {
        echo "Produit non trouvé.";
    }
} else {
    echo "ID de produit non spécifié.";
}

include 'footer.php';
?>