<?php
$servername = "localhost";
$username = "wolfpac";
$password = "devilhel76";
$dbname = "wolfgamestore";

// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Échec de la connexion à la base de données: " . $conn->connect_error);
}
?>