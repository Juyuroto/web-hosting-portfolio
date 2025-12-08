<?php
include "config.php";

$id = $_GET['id'] ?? null;
if (!$id) die("ID manquant");

try {
    $sql = "DELETE FROM contacts WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    header("Location: index.php?msg=Message supprimé avec succès");
    exit();
} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
