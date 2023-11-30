<?php
session_start();
include 'header.php';
include 'config.php';

// Vérifier si l'utilisateur est administrateur
if ($_SESSION['role'] !== 'admin') {
    header("Location: acces-refuse.php");
    exit();
}

// Traitement de la soumission du formulaire d'ajout
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter'])) {
    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $prix = $_POST['prix'];
    $description_complete = $_POST['description_complete'];
    $quantite_restante = $_POST['quantite_restante'];

    // Gestion du téléchargement de l'image
    $image_path = "uploads/";
    $image_filename = $_FILES['image']['name'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_target = $image_path . basename($image_filename);
    move_uploaded_file($_FILES['image']['tmp_name'], $image_path);

    // Vérifiez si le fichier est une image
if (getimagesize($image_tmp_name) === false) {
    echo "Le fichier n'est pas une image.";
    exit();
}

    // Vérifier s'il y a des erreurs lors du téléchargement
if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo "Erreur lors du téléchargement du fichier";
    // Afficher le code d'erreur : $_FILES['image']['error']
} else {
    // Déplacer le fichier téléchargé vers le répertoire de destination
    if (move_uploaded_file($image_tmp_name, $image_target)) {
        echo "Fichier déplacé avec succès vers " . $image_target;
    } else {
        echo "Échec du déplacement du fichier";
    }
}

    // Ajouter le produit à la base de données
    $stmt = $conn->prepare("INSERT INTO produits (nom, description, prix, image, description_complete, quantite_restante) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdsss", $nom, $description, $prix, $image_filename, $description_complete, $quantite_restante);

    if ($stmt->execute()) {
        echo "Produit ajouté avec succès!";
    } else {
        echo "Erreur lors de l'ajout du produit: " . $stmt->error;
    }

    $stmt->close();
}

// Traitement de la soumission du formulaire de modification
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modifier'])) {
    $produit_id = $_POST['produit_id'];
    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $prix = $_POST['prix'];
    $image = $_POST['image'];
    $description_complete = $_POST['description_complete'];
    $quantite_restante = $_POST['quantite_restante'];
    $mis_en_avant = isset($_POST['mis_en_avant']) ? 1 : 0;

    // Mettre à jour le produit dans la base de données
    $stmt = $conn->prepare("UPDATE produits SET nom=?, description=?, prix=?, image=?, description_complete=?, quantite_restante=?, mis_en_avant=? WHERE id=?");
    $stmt->bind_param("ssdssiii", $nom, $description, $prix, $image, $description_complete, $quantite_restante, $mis_en_avant, $produit_id);

    if ($stmt->execute()) {
        echo "Produit modifié avec succès!";
    } else {
        echo "Erreur lors de la modification du produit: " . $stmt->error;
    }

    $stmt->close();
}

// Traitement de la suppression d'un produit
if (isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['id'])) {
    $produit_id = $_GET['id'];

    // Supprimer le produit de la base de données
    $stmt = $conn->prepare("DELETE FROM produits WHERE id = ?");
    $stmt->bind_param("i", $produit_id);

    if ($stmt->execute()) {
        echo "Produit supprimé avec succès!";
    } else {
        echo "Erreur lors de la suppression du produit: " . $stmt->error;
    }

    $stmt->close();
}

// Afficher la liste des produits existants
$result = $conn->query("SELECT * FROM produits");

if ($result->num_rows > 0) {
    echo "<div class='my-4'>";
    echo "<h2 class='text-2xl font-bold mb-2'>Liste des produits</h2>";
    echo "<div class='overflow-x-auto'>";
    echo "<table class='min-w-full bg-white border'>";
    echo "<thead class='bg-gray-800 text-white'>";
    echo "<tr><th class='p-2'>ID</th><th class='p-2'>Nom</th><th class='p-2'>Description</th><th class='p-2'>Prix</th><th class='p-2'>Image</th><th class='p-2'>Description complète</th><th class='p-2'>Quantité restante</th><th class='p-2'>Action</th></tr>";
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
        echo "<td class='p-2'>";
        echo "<a href='admin-produits.php?action=modifier&id=" . $row['id'] . "' class='text-blue-500'>Modifier</a> | ";
        echo "<a href='admin-produits.php?action=supprimer&id=" . $row['id'] . "' class='text-red-500'>Supprimer</a></td>";
        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";
    echo "</div>";
    echo "</div>";
} else {
    echo "<p class='text-gray-700'>Aucun produit trouvé.</p>";
}

// Formulaire d'ajout
?> 
<?php
echo "<div class='my-4'>";
echo "<h2 class='text-2xl font-bold mb-4'>" . (isset($_GET['action']) && $_GET['action'] == 'modifier' ? 'Modifier le produit' : 'Ajouter un nouveau produit') . "</h2>";
echo "<form method='post' action='' enctype='multipart/form-data' class='bg-white p-4 rounded-lg shadow-md'>";
   
    // Formulaire d'ajout par défaut
    if (!(isset($_GET['action']) && $_GET['action'] == 'modifier' && isset($_GET['id']))) {
    ?>
        <div class="mb-4">
        <label for="nom" class="block text-sm font-medium text-gray-700">Nom:</label>
        <input type="text" name="nom" required class="mt-1 p-2 w-full border rounded-md">
    </div>

    <div class="mb-4">
        <label for="description" class="block text-sm font-medium text-gray-700">Description:</label>
        <input type="text" name="description" required class="mt-1 p-2 w-full border rounded-md">
    </div>

    <div class="mb-4">
        <label for="prix" class="block text-sm font-medium text-gray-700">Prix:</label>
        <input type="number" step="0.01" name="prix" required class="mt-1 p-2 w-full border rounded-md">
    </div>

    <div class="mb-4">
        <label for="image" class="block text-sm font-medium text-gray-700">Image:</label>
        <input type="file" name="image" accept="image/*" required class="mt-1 p-2 w-full border rounded-md">
    </div>

    <div class="mb-4">
        <label for="description_complete" class="block text-sm font-medium text-gray-700">Description complète:</label>
        <textarea name="description_complete" required class="mt-1 p-2 w-full border rounded-md"></textarea>
    </div>

    <div class="mb-4">
        <label for="quantite_restante" class="block text-sm font-medium text-gray-700">Quantité restante:</label>
        <input type="number" name="quantite_restante" required class="mt-1 p-2 w-full border rounded-md">
    </div>

    <div class="mb-4">
        <input type="submit" name="ajouter" value="Ajouter" class="bg-blue-500 text-white p-2 rounded-md cursor-pointer">
    </div>
    <?php
    } else {
        // Formulaire de modification
        $produit_id = $_GET['id'];
        $result = $conn->query("SELECT * FROM produits WHERE id = $produit_id");

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
    ?>
                <form method="post" action="" class="bg-white p-4 rounded-lg shadow-md">
        <input type="hidden" name="produit_id" value="<?php echo $row['id']; ?>">

        <div class="mb-4">
            <label for="nom" class="block text-sm font-medium text-gray-700">Nom:</label>
            <input type="text" name="nom" value="<?php echo $row['nom']; ?>" required class="mt-1 p-2 w-full border rounded-md">
        </div>

        <div class="mb-4">
            <label for="description" class="block text-sm font-medium text-gray-700">Description:</label>
            <input type="text" name="description" value="<?php echo $row['description']; ?>" required class="mt-1 p-2 w-full border rounded-md">
        </div>

        <div class="mb-4">
            <label for="prix" class="block text-sm font-medium text-gray-700">Prix:</label>
            <input type="number" step="0.01" name="prix" value="<?php echo $row['prix']; ?>" required class="mt-1 p-2 w-full border rounded-md">
        </div>

        <?php
        // Afficher l'image actuelle avec un lien pour la supprimer si elle existe
        if (!empty($row['image'])) {
            echo "<p class='mb-2'>Image actuelle:</p>";
            echo "<img src='uploads/" . $row['image'] . "' alt='" . $row['nom'] . "' width='100' class='mb-4'>";
            echo "<label class='mb-2'><input type='checkbox' name='supprimer_image'> Supprimer l'image actuelle</label>";
        }
        ?>

        <div class="mb-4">
            <label for="nouvelle_image" class="block text-sm font-medium text-gray-700">Nouvelle image:</label>
            <input type="file" name="nouvelle_image" accept="image/*" class="mt-1 p-2 w-full border rounded-md">
        </div>

        <div class="mb-4">
            <label for="description_complete" class="block text-sm font-medium text-gray-700">Description complète:</label>
            <textarea name="description_complete" required class="mt-1 p-2 w-full border rounded-md"><?php echo $row['description_complete']; ?></textarea>
        </div>

        <div class="mb-4">
            <label for="quantite_restante" class="block text-sm font-medium text-gray-700">Quantité restante:</label>
            <input type="number" name="quantite_restante" value="<?php echo $row['quantite_restante']; ?>" required class="mt-1 p-2 w-full border rounded-md">
        </div>

        <div class="mb-4">
            <label for="mis_en_avant" class="block text-sm font-medium text-gray-700">Mettre en avant :</label>
            <input type="checkbox" name="mis_en_avant" <?php echo $row['mis_en_avant'] ? 'checked' : ''; ?>>
        </div>

        <div class="mb-4">
            <input type="submit" name="modifier" value="Modifier" class="bg-blue-500 text-white p-2 rounded-md cursor-pointer">
        </div>
    </form>
    <?php
        }
    }
    ?>
</form>

<?php
include 'footer.php';
?>
