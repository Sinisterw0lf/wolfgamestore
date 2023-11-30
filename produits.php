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
<form class="my-4 p-4 bg-gray-800 text-white" method="post" action="produits.php">
    <label for="search" class="block mb-2">Rechercher un produit :</label>
    <div class="flex">
        <input type="text" name="search" id="search" class="border p-2 flex-grow mr-2" required>
        <input type="submit" value="Rechercher" class="bg-blue-500 text-white p-2">
    </div>
</form>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $search_term = $_POST['search'];

    // Requête SQL pour rechercher les produits correspondant au terme de recherche
    $sql = "SELECT * FROM produits WHERE nom LIKE '%$search_term%' OR description LIKE '%$search_term%'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<a href='produits.php' class='mb-4 text-blue-500'>&lt;- Retour</a>";
        echo "<h2 class='text-2xl font-bold mb-2'>Résultats de la recherche</h2>";
        echo "<div class='overflow-x-auto'>";
        echo "<table class='min-w-full bg-white border'>";
        echo "<thead class='bg-gray-800 text-white'>";
        echo "<tr><th class='p-2'>ID</th><th class='p-2'>Nom</th><th class='p-2'>Description</th><th class='p-2'>Prix</th><th class='p-2'>Image</th><th class='p-2'>Description complète</th><th class='p-2'>Quantité restante</th></tr>";
        echo "</thead>";
        echo "<tbody>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td class='p-2'>" . $row['id'] . "</td>";
            echo "<td class='p-2'><a href='fiche-produit.php?id=" . $row['id'] . "' class='text-blue-500'>" . $row['nom'] . "</a></td>";
            echo "<td class='p-2'>" . $row['description'] . "</td>";
            echo "<td class='p-2'>" . $row['prix'] . "</td>";
            echo "<td class='p-2'><img src='uploads/" . $row['image'] . "' alt='" . $row['nom'] . "' width='50'></td>";
            echo "<td class='p-2'>" . $row['description_complete'] . "</td>";
            echo "<td class='p-2'>" . $row['quantite_restante'] . "</td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
    } else {
        echo "<p class='text-gray-700'>Aucun résultat trouvé.</p>";
    }

    // Terminer le script pour éviter d'afficher le reste de la page
    exit();
}

// Récupérer la liste des produits depuis la base de données
$result = $conn->query("SELECT * FROM produits");

if ($result->num_rows > 0) {
    echo "<h2 class='text-2xl font-bold mb-4'>Produits mis en avant</h2>";
    echo "<div class='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4'>";
    while ($row = $result->fetch_assoc()) {
        if ($row['mis_en_avant']) {
            echo "<div class='bg-white p-4 rounded-lg shadow-md'>";
            echo "<a href='fiche-produit.php?id=" . $row['id'] . "' class='block mb-2 font-bold text-blue-500'>" . $row['nom'] . "</a>";
            echo "<p class='mb-2'>" . $row['description'] . "</p>";
            echo "<p class='text-gray-700'>Prix: " . $row['prix'] . " €</p>";
            echo "<img src='uploads/" . $row['image'] . "' alt='" . $row['nom'] . "' class='mt-2 w-full'>";
            echo "</div>";
        }
    }
    echo "</div>";
}

// Afficher la liste complète des produits
echo "<h2 class='text-2xl font-bold mb-4'>Tous les produits</h2>";
$result = $conn->query("SELECT * FROM produits");

if ($result->num_rows > 0) {
    echo "<div class='overflow-x-auto'>";
    echo "<table class='min-w-full bg-white border'>";
    echo "<thead class='bg-gray-800 text-white'>";
    echo "<tr><th class='p-2'>Nom</th><th class='p-2'>Description</th><th class='p-2'>Prix</th><th class='p-2'>Image</th><th class='p-2'>Description complète</th><th class='p-2'>Quantité restante</th></tr>";
    echo "</thead>";
    echo "<tbody>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td class='p-2'><a href='fiche-produit.php?id=" . $row['id'] . "' class='text-blue-500'>" . $row['nom'] . "</a></td>";
        echo "<td class='p-2'>" . $row['description'] . "</td>";
        echo "<td class='p-2'>" . $row['prix'] . "</td>";
        echo "<td class='p-2'><img src='uploads/" . $row['image'] . "' alt='" . $row['nom'] . "' width='50'></td>";
        echo "<td class='p-2'>" . $row['description_complete'] . "</td>";
        echo "<td class='p-2'>" . $row['quantite_restante'] . "</td>";
        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";
    echo "</div>";
} else {
    echo "<p class='text-gray-700'>Aucun produit trouvé.</p>";
}

$conn->close();
include 'footer.php';
?>
