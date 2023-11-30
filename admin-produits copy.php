<?php
session_start();
include 'header.php';
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: acces-refuse.php");
    exit();
}

include 'config.php';

// Ajouter un produit
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter_produit'])) {
    $nom_produit = $_POST['nom_produit'];
    $description_produit = $_POST['description_produit'];
    $prix_produit = $_POST['prix_produit'];

    $stmt = $conn->prepare("INSERT INTO produits (nom, description, prix) VALUES (?, ?, ?)");
    $stmt->bind_param("ssd", $nom_produit, $description_produit, $prix_produit);

    if ($stmt->execute()) {
        echo "Produit ajouté avec succès.";
    } else {
        echo "Erreur lors de l'ajout du produit: " . $stmt->error;
    }

    $stmt->close();
}

// Modifier un produit
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modifier_produit'])) {
    $produit_id = $_POST['produit_id'];
    $nouveau_nom_produit = $_POST['nouveau_nom_produit'];
    $nouvelle_description_produit = $_POST['nouvelle_description_produit'];
    $nouveau_prix_produit = $_POST['nouveau_prix_produit'];

    $stmt = $conn->prepare("UPDATE produits SET nom = ?, description = ?, prix = ? WHERE id = ?");
    $stmt->bind_param("ssdi", $nouveau_nom_produit, $nouvelle_description_produit, $nouveau_prix_produit, $produit_id);

    if ($stmt->execute()) {
        echo "Produit modifié avec succès.";
    } else {
        echo "Erreur lors de la modification du produit: " . $stmt->error;
    }

    $stmt->close();
}

// Supprimer un produit
if (isset($_GET['supprimer_produit'])) {
    $produit_id = $_GET['supprimer_produit'];

    $stmt = $conn->prepare("DELETE FROM produits WHERE id = ?");
    $stmt->bind_param("i", $produit_id);

    if ($stmt->execute()) {
        echo "Produit supprimé avec succès.";
    } else {
        echo "Erreur lors de la suppression du produit: " . $stmt->error;
    }

    $stmt->close();
}

// Récupérer la liste des produits depuis la base de données
$result = $conn->query("SELECT * FROM produits");

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "Nom: " . $row['nom'] . " - Description: " . $row['description'] . " - Prix: $" . $row['prix'] . " ";
        echo "<a href='admin-produits.php?modifier_produit=" . $row['id'] . "'>Modifier</a> ";
        echo "<a href='admin-produits.php?supprimer_produit=" . $row['id'] . "'>Supprimer</a><br>";
    }
} else {
    echo "Aucun produit trouvé.";
}

$conn->close();
?>

<!-- Formulaire d'ajout et de modification de produit -->
<form method="post" action="admin-produits.php">
    <?php
    if (isset($_GET['modifier_produit'])) {
        $produit_id = $_GET['modifier_produit'];
        $result = $conn->query("SELECT * FROM produits WHERE id = $produit_id");

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            echo "<input type='hidden' name='produit_id' value='" . $produit_id . "'>";
            echo "Nouveau Nom: <input type='text' name='nouveau_nom_produit' value='" . $row['nom'] . "'><br>";
            echo "Nouvelle Description: <input type='text' name='nouvelle_description_produit' value='" . $row['description'] . "'><br>";
            echo "Nouveau Prix: <input type='text' name='nouveau_prix_produit' value='" . $row['prix'] . "'><br>";
            echo "<input type='submit' name='modifier_produit' value='Modifier'>";
        }
    } else {
        echo "Nom du Produit: <input type='text' name='nom_produit'><br>";
        echo "Description du Produit: <input type='text' name='description_produit'><br>";
        echo "Prix du Produit: <input type='text' name='prix_produit'><br>";
        echo "<input type='submit' name='ajouter_produit' value='Ajouter'>";
    }
    ?>
</form>