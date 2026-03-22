<?php
// check_and_update_db.php
require_once 'config/database.php';

echo "<h2>🔧 Mise à jour de la base de données</h2>";

try {
    // 1. Vérifier la structure actuelle
    echo "<h3>Structure actuelle de la table 'complaints' :</h3>";
    $describe = $conn->query("DESCRIBE complaints");
    $columns = $describe->fetchAll(PDO::FETCH_ASSOC);
    
    $existing_columns = [];
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Colonne</th><th>Type</th><th>Null</th><th>Défaut</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . ($column['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
        $existing_columns[] = $column['Field'];
    }
    echo "</table>";
    
    // 2. Ajouter les colonnes manquantes
    echo "<h3>Mise à jour de la table :</h3>";
    
    if (!in_array('admin_comment', $existing_columns)) {
        $conn->exec("ALTER TABLE complaints ADD COLUMN admin_comment TEXT NULL");
        echo "✅ Colonne 'admin_comment' ajoutée<br>";
    } else {
        echo "⏭️ Colonne 'admin_comment' existe déjà<br>";
    }
    
    if (!in_array('reviewed_at', $existing_columns)) {
        $conn->exec("ALTER TABLE complaints ADD COLUMN reviewed_at TIMESTAMP NULL");
        echo "✅ Colonne 'reviewed_at' ajoutée<br>";
    } else {
        echo "⏭️ Colonne 'reviewed_at' existe déjà<br>";
    }
    
    // 3. Vérifier/modifier la colonne status
    if (in_array('status', $existing_columns)) {
        echo "<h3>Vérification de la colonne 'status' :</h3>";
        
        // Voir les valeurs actuelles possibles
        $status_check = $conn->query("SHOW COLUMNS FROM complaints WHERE Field = 'status'");
        $status_col = $status_check->fetch(PDO::FETCH_ASSOC);
        
        echo "Type actuel : " . $status_col['Type'] . "<br>";
        echo "Valeur par défaut : " . ($status_col['Default'] ?? 'NULL') . "<br>";
        
        // Optionnel: Modifier si nécessaire
        $confirm = isset($_GET['modify_status']) ? $_GET['modify_status'] : false;
        
        if ($confirm === 'yes') {
            try {
                $conn->exec("ALTER TABLE complaints MODIFY COLUMN status ENUM('en_attente', 'validee', 'rejetee') DEFAULT 'en_attente'");
                echo "✅ Colonne 'status' modifiée avec succès<br>";
            } catch(PDOException $e) {
                echo "⚠️ Note: " . $e->getMessage() . "<br>";
            }
        } else {
            echo "<a href='?modify_status=yes' onclick='return confirm(\"Voulez-vous vraiment modifier la colonne status?\")'>🔧 Modifier la colonne status</a><br>";
        }
    }
    
    echo "<h3 style='color:green;'>✅ Vérification terminée !</h3>";
    
} catch(PDOException $e) {
    echo "<h3 style='color:red;'>❌ Erreur : " . $e->getMessage() . "</h3>";
}
?>