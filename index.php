<?php
session_start();
include 'header.php';
include 'config.php';


if (isset($_GET['deconnexion'])) {
    session_destroy();
    header("Location: connexion.php");
    exit();
}

if (!isset($_SESSION['id'])) {
    header("Location: connexion.php");
    exit();
}

$role = $_SESSION['role'];

if ($role === 'admin') {
    echo "<div class='p-4 bg-gray-800 text-white'>";
    echo "<p class='mb-2'>Bienvenue, administrateur!</p>";
    echo "<a href='produits.php' class='mr-2 text-blue-500'>Voir la liste des produits</a>";
    echo "<a href='admin-produits.php' class='mr-2 text-blue-500'>Gérer les produits</a>";
    echo "<a href='historique-commandes.php' class='mr-2 text-blue-500'>Historique des commandes</a>";
    echo "<a href='panier.php' class='mr-2 text-blue-500'>Voir le panier</a>";
    echo "<a href='index.php?deconnexion=true' class='text-red-500'>Se déconnecter</a>";
    echo "</div>";
} else {
    echo "<div class='p-4 bg-gray-800 text-white'>";
    echo "<p class='mb-2'>Bienvenue, utilisateur!</p>";
    echo "<a href='produits.php' class='mr-2 text-blue-500'>Voir la liste des produits</a>";
    echo "<a href='panier.php' class='mr-2 text-blue-500'>Voir le panier</a>";
    echo "<a href='historique-commandes.php' class='mr-2 text-blue-500'>Historique des commandes</a>";
    echo "<a href='index.php?deconnexion=true' class='text-red-500'>Se déconnecter</a>";
    echo "</div>";
}

// Récupérer les produits mis en avant
$sqlMisEnAvant = "SELECT * FROM produits WHERE mis_en_avant = 1";
$resultMisEnAvant = $conn->query($sqlMisEnAvant);

// Afficher les produits mis en avant
echo "<div class='my-4'>";
echo "<h2 class='text-xl font-bold mb-2'>Produits mis en avant</h2>";
if ($resultMisEnAvant->num_rows > 0) {
    echo "<div class='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4'>";
    while ($row = $resultMisEnAvant->fetch_assoc()) {
        echo "<div class='bg-white p-4 rounded-lg shadow-md'>";
        echo "<a href='fiche-produit.php?id=" . $row['id'] . "' class='block mb-2 font-bold text-blue-500'>" . $row['nom'] . "</a>";
        echo "<p class='mb-2'>" . $row['description'] . "</p>";
        echo "<p class='text-gray-700'>Prix: " . $row['prix'] . " €</p>";
        echo "<img src='uploads/" . $row['image'] . "' alt='" . $row['nom'] . "' class='mt-2 w-full'>";
        echo "</div>";
    }
    echo "</div>";
} else {
    echo "<p class='text-gray-700'>Aucun produit mis en avant trouvé.</p>";
}
echo "</div>";

// Récupérer les derniers produits ajoutés
$sqlDerniersProduits = "SELECT * FROM produits ORDER BY id DESC LIMIT 3"; // Vous pouvez ajuster le nombre de produits affichés ici
$resultDerniersProduits = $conn->query($sqlDerniersProduits);

// Afficher les derniers produits ajoutés
echo "<div class='my-4'>";
echo "<h2 class='text-xl font-bold mb-2'>Derniers produits ajoutés</h2>";
if ($resultDerniersProduits->num_rows > 0) {
    echo "<div class='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4'>";
    while ($row = $resultDerniersProduits->fetch_assoc()) {
        echo "<div class='bg-white p-4 rounded-lg shadow-md'>";
        echo "<a href='fiche-produit.php?id=" . $row['id'] . "' class='block mb-2 font-bold text-blue-500'>" . $row['nom'] . "</a>";
        echo "<p class='mb-2'>" . $row['description'] . "</p>";
        echo "<p class='text-gray-700'>Prix: " . $row['prix'] . " €</p>";
        echo "<img src='uploads/" . $row['image'] . "' alt='" . $row['nom'] . "' class='mt-2 w-full'>";
        echo "</div>";
    }
    echo "</div>";
} else {
    echo "<p class='text-gray-700'>Aucun dernier produit ajouté trouvé.</p>";
}
echo "</div>";

include 'footer.php';
?>
