<?php
namespace App\Models;

require_once __DIR__ . '/../config/database.php';

use App\Config\Database;

class Contact {
    public function save(string $name, string $email, string $tel, string $subject, string $message): bool {
        $db = Database::connect();
        
        $sql = "INSERT INTO contacts (name, email, tel, subject, message, created_at) 
                VALUES (:name, :email, :tel, :subject, :message, NOW())";
        
        $stmt = $db->prepare($sql);
        
        return $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':tel' => $tel,
            ':subject' => $subject,
            ':message' => $message
        ]);
    }
}