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
    header("Location: acces-refuse.php");
    exit();
}

?>
<!-- Formulaire de recherche -->
<form method="post" action="produits.php">
    <label for="search">Rechercher un produit :</label>
    <input type="text" name="search" id="search" required>
    <input type="submit" value="Rechercher">
</form>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $search_term = $_POST['search'];

    // Requête SQL pour rechercher les produits correspondant au terme de recherche
    $sql = "SELECT * FROM produits WHERE nom LIKE '%$search_term%' OR description LIKE '%$search_term%'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<a href='produits.php'><- Retour<a/>";
        echo "<h2>Résultats de la recherche</h2>";
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Nom</th><th>Description</th><th>Prix</th><th>Image</th><th>Description complète</th><th>Quantité restante</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td><a href='fiche-produit.php?id=" . $row['id'] . "'>" . $row['nom'] . "</a></td>";
            echo "<td>" . $row['description'] . "</td>";
            echo "<td>" . $row['prix'] . "</td>";
            echo "<td><img src='uploads/" . $row['image'] . "' alt='" . $row['nom'] . "' width='50'></td>";
            echo "<td>" . $row['description_complete'] . "</td>";
            echo "<td>" . $row['quantite_restante'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "Aucun résultat trouvé.";
    }

    // Terminer le script pour éviter d'afficher le reste de la page
    exit();
}

// Récupérer la liste des produits depuis la base de données
$result = $conn->query("SELECT * FROM produits");

if ($result->num_rows > 0) {
    echo "<h2>Produits mis en avant</h2>";
    echo "<table border='1'>";
    echo "<tr><th>Nom</th><th>Description</th><th>Prix</th><th>Image</th><th>Description complète</th><th>Quantité restante</th></tr>";
    while ($row = $result->fetch_assoc()) {
        if ($row['mis_en_avant']) {
            echo "<tr>";
            echo "<td><a href='fiche-produit.php?id=" . $row['id'] . "'>" . $row['nom'] . "</a></td>";;
            echo "<td>" . $row['description'] . "</td>";
            echo "<td>" . $row['prix'] . "</td>";
            echo "<td><img src='uploads/" . $row['image'] . "' alt='" . $row['nom'] . "' width='50'></td>";
            echo "<td>" . $row['description_complete'] . "</td>";
            echo "<td>" . $row['quantite_restante'] . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
}

// Afficher la liste complète des produits
echo "<h2>Tous les produits</h2>";
$result = $conn->query("SELECT * FROM produits");

if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Nom</th><th>Description</th><th>Prix</th><th>Image</th><th>Description complète</th><th>Quantité restante</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td><a href='fiche-produit.php?id=" . $row['id'] . "'>" . $row['nom'] . "</a></td>";
        echo "<td>" . $row['description'] . "</td>";
        echo "<td>" . $row['prix'] . "</td>";
        echo "<td><img src='uploads/" . $row['image'] . "' alt='" . $row['nom'] . "' width='50'></td>";
        echo "<td>" . $row['description_complete'] . "</td>";
        echo "<td>" . $row['quantite_restante'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Aucun produit trouvé.";
}

$conn->close();
?>