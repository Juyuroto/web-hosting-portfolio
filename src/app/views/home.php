<?php
$name = $_POST['name'];
$email = $_POST['email'];
$number = $_POST['tel'];
$subject = $_POST['subject'];
$message = $_POST['message'];

try {
    $dsn = 'mysql:host=db;port=3306;dbname=mydb;charset=utf8mb4';
    $username = 'myuser';
    $password = 'mypassword';

    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Préparation et exécution
    $stmt = $pdo->prepare("INSERT INTO contacts (name, email, number, subject, message) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $email, $number, $subject, $message]);

    echo "New record created successfully";

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
