<?php
// complaints.php
session_start();
require_once "config/database.php";
require_once "includes/auth.php";

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Filtrage optionnel par statut
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// ==== Récupération des plaintes ====
// Pour admin : toutes les plaintes
if ($role === 'admin') {
    if ($status_filter) {
        $stmt = $pdo->prepare("SELECT c.id, c.title, c.status, u.name AS user_name 
                               FROM complaints c 
                               JOIN users u ON c.user_id = u.id 
                               WHERE c.status = :status
                               ORDER BY c.created_at DESC");
        $stmt->bindParam(':status', $status_filter, PDO::PARAM_STR);
    } else {
        $stmt = $pdo->query("SELECT c.id, c.title, c.status, u.name AS user_name 
                             FROM complaints c 
                             JOIN users u ON c.user_id = u.id 
                             ORDER BY c.created_at DESC");
    }
} 
// Pour user : ses propres plaintes
else {
    if ($status_filter) {
        $stmt = $pdo->prepare("SELECT id, title, status FROM complaints 
                               WHERE user_id = :user_id AND status = :status 
                               ORDER BY created_at DESC");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status_filter, PDO::PARAM_STR);
    } else {
        $stmt = $pdo->prepare("SELECT id, title, status FROM complaints 
                               WHERE user_id = :user_id 
                               ORDER BY created_at DESC");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    }
}

// Exécution
$stmt->execute();
$complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Plaintes | Modern Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "includes/header.php"; ?>

<main class="main-content">
    <div class="container">
        <div class="page-header">
            <div>
                <h1>Gestion des Plaintes</h1>
                <p class="text-muted">Consultez et gérez l'état de vos demandes.</p>
            </div>
            <div class="header-actions">
                <a href="add_complaint.php" class="btn btn-primary">+ Nouvelle Plainte</a>
                <a href="dashboard.php" class="btn btn-secondary">Tableau de bord</a>
            </div>
        </div>

        <div class="filter-card">
            <form method="get" action="complaints.php" class="filter-form">
                <label for="status">Filtrer par état :</label>
                <div class="filter-group">
                    <select name="status" id="status">
                        <option value="">Tous les statuts</option>
                        <option value="pending" <?php if($status_filter==='pending') echo 'selected'; ?>>En attente</option>
                        <option value="resolved" <?php if($status_filter==='resolved') echo 'selected'; ?>>Résolu</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-filter">Appliquer</button>
                </div>
            </form>
        </div>

        <div class="table-container">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titre de la plainte</th>
                        <?php if($role==='admin') echo "<th>Utilisateur</th>"; ?>
                        <th>Statut</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($complaints): ?>
                        <?php foreach ($complaints as $complaint): ?>
                            <tr>
                                <td class="text-bold">#<?php echo $complaint['id']; ?></td>
                                <td><?php echo htmlspecialchars($complaint['title']); ?></td>
                                <?php if($role==='admin') echo "<td><span class='user-tag'>".htmlspecialchars($complaint['user_name'])."</span></td>"; ?>
                                <td>
                                    <span class="badge status-<?php echo $complaint['status']; ?>">
                                        <?php echo ($complaint['status'] === 'pending') ? 'En attente' : 'Résolu'; ?>
                                    </span>
                                </td>
                                <td class="text-right">
                                    <a href="edit_complaint.php?id=<?php echo $complaint['id']; ?>" class="btn-edit">Modifier</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo ($role==='admin') ? 5 : 4; ?>" class="empty-state">
                                Aucune plainte trouvée.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include "includes/footer.php"; ?>
</body>
</html>
