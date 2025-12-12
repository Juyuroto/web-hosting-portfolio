<?php
// Routing simple
$page = $_GET['page'] ?? 'home';

switch($page) {
    case 'me':
        require_once __DIR__ . '/../src/views/me.php';
        break;
    case 'contact':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../src/controllers/ContactController.php';
            $controller = new \App\Controllers\ContactController();
            $controller->submit();
            exit;
        }
        break;
    default:
        require_once __DIR__ . '/../src/views/home.php';
}