<?php
include 'header.php';
include 'config.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];

    $stmt = $conn->prepare("SELECT id, nom, mot_de_passe, role FROM utilisateurs WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($id, $nom, $mot_de_passe_hash, $role);

    if ($stmt->fetch() && password_verify($mot_de_passe, $mot_de_passe_hash)) {
        session_start();
        $_SESSION['id'] = $id;
        $_SESSION['nom'] = $nom;
        $_SESSION['role'] = $role;

        echo "Connexion réussie. Redirection vers la page d'accueil...";
        header("Location: index.php");
    } else {
        echo "Email ou mot de passe incorrect.";
    }

    $stmt->close();
}

$conn->close();
?>

<!-- Formulaire de connexion -->
<form class="p-5 container mx-auto max-w-md bg-white rounded-lg shadow-md" method="post" action="connexion.php">
    <div class="mb-4">
        <label for="email" class="block text-sm font-medium text-gray-600">Email:</label>
        <input type="email" name="email" id="email" class="mt-1 p-2 w-full border rounded-md">
    </div>

    <div class="mb-4">
        <label for="mot_de_passe" class="block text-sm font-medium text-gray-600">Mot de passe:</label>
        <input type="password" name="mot_de_passe" id="mot_de_passe" class="mt-1 p-2 w-full border rounded-md">
    </div>

    <div class="mb-4">
        <input type="submit" value="Se connecter" class="bg-blue-500 text-white p-2 rounded-md cursor-pointer">
    </div>
</form>

<p class="text-center mt-4">Vous n'avez pas de compte? <a href="inscription.php" class="text-blue-500">Inscrivez-vous ici</a>.</p>
<?php include 'footer.php'; ?>