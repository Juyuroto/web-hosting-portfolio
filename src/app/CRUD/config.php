<?php
$host = "db";              // Nom du service DB dans docker-compose
$dbname = "mydb";          // Nom de la base
$username = "myuser";      // Utilisateur MySQL
$password = "mypassword";  // Mot de passe MySQL

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
