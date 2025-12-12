<?php
namespace App\Controllers;

class HomeController {
    public function index(): void {
        require_once __DIR__ . '/../views/home.php';
    }
}