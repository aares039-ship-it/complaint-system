<?php
// dashboard.php
session_start();
require_once "config/database.php";
require_once "includes/auth.php"; // protège la page si non connecté

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Initialisation variables statistiques
$total_complaints = $pending_complaints = $resolved_complaints = 0;

// ==== Pour admin ====
if ($role === 'admin') {
    // Total complaints
    $stmt = $pdo->query("SELECT COUNT(*) FROM complaints");
    $total_complaints = $stmt->fetchColumn();

    // Pending complaints
    $stmt = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'pending'");
    $pending_complaints = $stmt->fetchColumn();

    // Resolved complaints
    $stmt = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'resolved'");
    $resolved_complaints = $stmt->fetchColumn();
} 
// ==== Pour user ====
else {
    // Total complaints for this user
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $total_complaints = $stmt->fetchColumn();

    // Pending complaints
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE user_id = :user_id AND status = 'pending'");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $pending_complaints = $stmt->fetchColumn();

    // Resolved complaints
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE user_id = :user_id AND status = 'resolved'");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $resolved_complaints = $stmt->fetchColumn();
}

// Optionally fetch latest complaints (5 latest)
if ($role === 'admin') {
    $stmt = $pdo->query("SELECT c.id, c.title, c.status, u.name AS user_name 
                         FROM complaints c 
                         JOIN users u ON c.user_id = u.id 
                         ORDER BY c.created_at DESC LIMIT 5");
    $latest_complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->prepare("SELECT id, title, status FROM complaints 
                           WHERE user_id = :user_id 
                           ORDER BY created_at DESC LIMIT 5");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $latest_complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Gestion des Plaintes</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "includes/header.php"; ?>

<main class="main-content">
    <div class="container">
        <header class="dashboard-header">
            <div>
                <h1>Ravi de vous revoir, <?php echo htmlspecialchars($_SESSION['user_name']); ?> 👋</h1>
                <p class="text-muted">Voici un résumé de l'activité sur votre compte.</p>
            </div>
            <a href="logout.php" class="btn btn-secondary btn-logout">Déconnexion</a>
        </header>

        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-label">Total des plaintes</span>
                <div class="stat-value"><?php echo $total_complaints; ?></div>
                <div class="stat-indicator total"></div>
            </div>
            <div class="stat-card">
                <span class="stat-label">En attente</span>
                <div class="stat-value"><?php echo $pending_complaints; ?></div>
                <div class="stat-indicator pending"></div>
            </div>
            <div class="stat-card">
                <span class="stat-label">Résolues</span>
                <div class="stat-value text-success"><?php echo $resolved_complaints; ?></div>
                <div class="stat-indicator resolved"></div>
            </div>
        </div>

        <section class="latest-section">
            <div class="section-header">
                <h3>Plaintes récentes</h3>
                <a href="complaints.php" class="link-more">Voir tout →</a>
            </div>

            <div class="table-container">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <?php if($role === 'admin') echo "<th>Utilisateur</th>"; ?>
                            <th>Statut</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($latest_complaints as $complaint): ?>
                        <tr>
                            <td class="text-bold">#<?php echo $complaint['id']; ?></td>
                            <td><?php echo htmlspecialchars($complaint['title']); ?></td>
                            <?php if($role === 'admin') echo "<td><span class='user-tag'>".htmlspecialchars($complaint['user_name'])."</span></td>"; ?>
                            <td>
                                <span class="badge status-<?php echo $complaint['status']; ?>">
                                    <?php echo ($complaint['status'] === 'pending') ? 'En attente' : 'Résolu'; ?>
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="edit_complaint.php?id=<?php echo $complaint['id']; ?>" class="btn-edit">Détails</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>

<?php include "includes/footer.php"; ?>
</body>
</html>