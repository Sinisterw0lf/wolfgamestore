<?php
include 'header.php';
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);

    $result = $conn->query("SELECT COUNT(*) AS total FROM utilisateurs");
    if ($result) {
        $row = $result->fetch_assoc();
        $total_utilisateurs = $row['total'];
    }
    $role = ($total_utilisateurs == 0) ? 'admin' : 'utilisateur';


    $stmt = $conn->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nom, $email, $mot_de_passe, $role);

    if ($stmt->execute()) {
        echo "Inscription réussie.";
    } else {
        echo "Erreur lors de l'inscription: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!-- Formulaire d'inscription -->
<form method="post" action="inscription.php">
    <label>Nom: <input type="text" name="nom" required></label><br>
    <label>Email: <input type="email" name="email" required></label><br>
    <label>Mot de passe: <input type="password" name="mot_de_passe" required></label><br>
    <input type="submit" value="S'inscrire">
</form>

<?php include 'footer.php'; ?>