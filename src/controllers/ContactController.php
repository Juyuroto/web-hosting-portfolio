<?php
namespace App\Controllers;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Contact.php';

use App\Models\Contact;

class ContactController {
    public function submit(): void {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
            exit;
        }
        
        $name = htmlspecialchars($_POST['name'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $tel = htmlspecialchars($_POST['tel'] ?? '');
        $subject = htmlspecialchars($_POST['subject'] ?? '');
        $message = htmlspecialchars($_POST['message'] ?? '');
        
        if (!$email || !$name || !$message) {
            echo json_encode(['success' => false, 'error' => 'Données invalides']);
            exit;
        }
        
        $contact = new Contact();
        if ($contact->save($name, $email, $tel, $subject, $message)) {
            echo json_encode(['success' => true, 'message' => 'Message envoyé avec succès']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'enregistrement']);
        }
    }
}