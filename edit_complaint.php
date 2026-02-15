<?php
// edit_complaint.php
session_start();
require_once "config/database.php";
require_once "includes/auth.php";

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Vérifier si l'ID de la plainte est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: complaints.php");
    exit();
}

$complaint_id = $_GET['id'];
$title = $description = $status = "";
$title_err = $description_err = $status_err = "";

// Récupérer la plainte
if ($role === 'admin') {
    $stmt = $pdo->prepare("SELECT * FROM complaints WHERE id = :id");
    $stmt->bindParam(':id', $complaint_id, PDO::PARAM_INT);
} else {
    $stmt = $pdo->prepare("SELECT * FROM complaints WHERE id = :id AND user_id = :user_id");
    $stmt->bindParam(':id', $complaint_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
}
$stmt->execute();
$complaint = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$complaint) {
    echo "Complaint not found or access denied.";
    exit();
}

// Initialisation des champs
$title = $complaint['title'];
$description = $complaint['description'];
$status = $complaint['status'];

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validation du titre
    if (empty(trim($_POST['title']))) {
        $title_err = "Please enter a title.";
    } else {
        $title = trim($_POST['title']);
    }

    // Validation description
    if (empty(trim($_POST['description']))) {
        $description_err = "Please enter a description.";
    } else {
        $description = trim($_POST['description']);
    }

    // Pour admin : statut
    if ($role === 'admin') {
        if (!isset($_POST['status']) || !in_array($_POST['status'], ['pending', 'resolved'])) {
            $status_err = "Invalid status.";
        } else {
            $status = $_POST['status'];
        }
    }

    // Si pas d'erreurs
    if (empty($title_err) && empty($description_err) && empty($status_err)) {
        if ($role === 'admin') {
            $sql = "UPDATE complaints SET title = :title, description = :description, status = :status WHERE id = :id";
        } else {
            // user ne peut modifier que si status = pending
            if ($complaint['status'] === 'resolved') {
                echo "You cannot edit a resolved complaint.";
                exit();
            }
            $sql = "UPDATE complaints SET title = :title, description = :description WHERE id = :id AND user_id = :user_id";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':id', $complaint_id, PDO::PARAM_INT);
        if ($role !== 'admin') $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        if ($role === 'admin') $stmt->bindParam(':status', $status, PDO::PARAM_STR);

        if ($stmt->execute()) {
            header("Location: complaints.php");
            exit();
        } else {
            echo "Something went wrong. Try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la plainte | Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "includes/header.php"; ?>

<main class="main-content">
    <div class="container-sm">
        <div class="card">
            <div class="card-header">
                <h2>Modifier la plainte #<?php echo $complaint_id; ?></h2>
                <p>Mettez à jour les informations ou changez le statut de la demande.</p>
            </div>
            
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $complaint_id); ?>" method="post" class="styled-form">
                
                <div class="form-group <?php echo (!empty($title_err)) ? 'has-error' : ''; ?>">
                    <label for="title">Titre</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" <?php echo ($role !== 'admin') ? '' : 'readonly class="readonly-input"'; ?>>
                    <span class="error-msg"><?php echo $title_err; ?></span>
                </div>

                <div class="form-group <?php echo (!empty($description_err)) ? 'has-error' : ''; ?>">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="5" <?php echo ($role !== 'admin') ? '' : 'readonly class="readonly-input"'; ?>><?php echo htmlspecialchars($description); ?></textarea>
                    <span class="error-msg"><?php echo $description_err; ?></span>
                </div>

                <?php if ($role === 'admin'): ?>
                <div class="admin-action-box">
                    <div class="form-group <?php echo (!empty($status_err)) ? 'has-error' : ''; ?>">
                        <label for="status">Changer le statut de la plainte</label>
                        <select name="status" id="status" class="status-select">
                            <option value="pending" <?php if($status==='pending') echo 'selected'; ?>>🟡 En attente (Pending)</option>
                            <option value="resolved" <?php if($status==='resolved') echo 'selected'; ?>>🟢 Résolu (Resolved)</option>
                        </select>
                        <span class="error-msg"><?php echo $status_err; ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                    <a href="complaints.php" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include "includes/footer.php"; ?>
</body>
</html>