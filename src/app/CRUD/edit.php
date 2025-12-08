<?php
include "config.php";

$id = $_GET["id"] ?? null;
if (!$id) die("ID manquant");

try {
    $sql = "SELECT * FROM contacts WHERE id = :id LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) die("Message introuvable.");
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

if(isset($_POST["submit"])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $number = $_POST['number'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    try {
        $sql = "UPDATE contacts SET name=:name, email=:email, number=:number, subject=:subject, message=:message WHERE id=:id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':number', $number);
        $stmt->bindParam(':subject', $subject);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        header("Location: index.php?msg=Message mis à jour avec succès");
        exit();
    } catch(PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Message</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../../public/assets/css/css/edit.css">
</head>
<body>
<nav class="navbar navbar-light justify-content-center fs-3 mb-5">Edit Message</nav>

<div class="container d-flex justify-content-center">
    <form action="" method="post" style="width:50vw; min-width:300px;">
        <div class="mb-3">
            <label class="form-label">Name:</label>
            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($row['name']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email:</label>
            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($row['email']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Number:</label>
            <input type="tel" class="form-control" name="number" value="<?= htmlspecialchars($row['number']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Subject:</label>
            <input type="text" class="form-control" name="subject" value="<?= htmlspecialchars($row['subject']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Message:</label>
            <textarea class="form-control" name="message" cols="30" rows="10" required><?= htmlspecialchars($row['message']) ?></textarea>
        </div>

        <div>
            <button type="submit" class="btn btn-success" name="submit">Update</button>
            <a href="index.php" class="btn btn-danger">Cancel</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
