<?php
// dashboard.php
require_once "../config/database.php";
require_once "../includes/auth.php"; // protège la page si non connecté

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
    $stmt = $pdo->query("SELECT c.id, c.title, c.status, u.fullname AS user_name 
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
    <title>Admin | Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<?php include "includes/header.php"; ?>

<main class="main-content">
    <div class="container">
        <header class="dashboard-header">
            <div>
                <h1>Nice to see you again, <?php echo htmlspecialchars($_SESSION['user_name']); ?> 👋</h1>
                <p class="text-muted">here is a summary of the activity on your account .</p>
            </div>
        </header>

        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-label">Total complaints</span>
                <div class="stat-value"><?php echo $total_complaints; ?></div>
                <div class="stat-indicator total"></div>
            </div>
            <div class="stat-card">
                <span class="stat-label">waiting for</span>
                <div class="stat-value"><?php echo $pending_complaints; ?></div>
                <div class="stat-indicator pending"></div>
            </div>
            <div class="stat-card">
                <span class="stat-label">Solved</span>
                <div class="stat-value text-success"><?php echo $resolved_complaints; ?></div>
                <div class="stat-indicator resolved"></div>
            </div>
        </div>

        <section class="latest-section">
            <div class="section-header">
                <h3>Complaints</h3>
                <a href="complaints.php" class="link-more">see all →</a>
            </div>

            <div class="table-container">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <?php if($role === 'admin') echo "<th>User</th>"; ?>
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
                                    <?php echo $complaint['status']; ?>
                                </span>
                            </td>
                            <td class="text-right" style="display: flex; gap: 10px; justify-content: flex-end; align-items: center;">
                                <select onchange="change_status(this.value, <?php echo $complaint['id']; ?>)" style="padding: 5px 10px; border-radius: 4px; border: 1px solid #ccc; cursor: pointer;">
                                    <option value="pending" <?php echo ($complaint['status'] == 'pending' || $complaint['status'] == 'en_attente') ? 'selected' : ''; ?>>En attente</option>
                                    <option value="inprogress" <?php echo ($complaint['status'] == 'inprogress') ? 'selected' : ''; ?>>En cours</option>
                                    <option value="resolved" <?php echo ($complaint['status'] == 'resolved') ? 'selected' : ''; ?>>Résolu</option>
                                    <option value="rejected" <?php echo ($complaint['status'] == 'rejected') ? 'selected' : ''; ?>>Rejeté</option>
                                </select>
                                <a href="view_complaint.php?complaint_id=<?php echo $complaint['id']?>" class="btn btn-primary" style="padding: 5px 10px;" title="Voir les détails"><i class="fa fa-eye"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>

<?php include "../includes/footer.php"; ?>

<script type="text/javascript">
  function change_status(value_to, complaint_id) {
    if(!confirm("Êtes-vous sûr de vouloir changer le statut de cette réclamation ?")) return;
    
    var action = "change_stauts";
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var msg = this.responseText.trim();
            if (msg == 'success') {
               alert("Opération réussie !");
               location.reload();
            } else {
              alert("L'opération a échoué ! (" + msg + ")");
            }
        }
    };
    xhttp.open("POST", "changeStatus.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("action=" + action + "&mvalue_to=" + value_to + "&mcomplaint_id=" + complaint_id);  
  }
</script>

</body>
</html>