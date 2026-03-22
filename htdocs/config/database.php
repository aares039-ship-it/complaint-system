<?php
$host = "sql100.infinityfree.com";
$db   = "if0_41277036_complaint_system";
$user = "if0_41277036";
$pass = "VF5xRNJotsBpLw";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
   $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}