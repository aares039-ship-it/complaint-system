<?php
// add_complaint.php
session_start();
require_once "config/database.php";
require_once "includes/auth.php";

$user_id = $_SESSION['user_id'];
$title = $description = "";
$title_err = $description_err = "";

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validation du titre
    if (empty(trim($_POST["title"]))) {
        $title_err = "Please enter a title for the complaint.";
    } else {
        $title = trim($_POST["title"]);
    }

    // Validation de la description
    if (empty(trim($_POST["description"]))) {
        $description_err = "Please enter a description.";
    } else {
        $description = trim($_POST["description"]);
    }

    // Si pas d'erreurs, insertion
    if (empty($title_err) && empty($description_err)) {
        $sql = "INSERT INTO complaints (user_id, title, description, status) 
                VALUES (:user_id, :title, :description, 'pending')";
        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);

            if ($stmt->execute()) {
                // Redirection vers liste des plaintes
                header("Location: complaints.php");
                exit();
            } else {
                echo "Something went wrong. Please try again later.";
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soumettre une plainte </title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "includes/header.php"; ?>

<main class="main-content">
    <div class="container-sm">
        <div class="card">
            <div class="card-header">
                <h2>Nouvelle Plainte</h2>
                <p>Décrivez le problème rencontré de manière précise.</p>
            </div>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="styled-form">
                <div class="form-group <?php echo (!empty($title_err)) ? 'has-error' : ''; ?>">
                    <label for="title">Titre du problème</label>
                    <input type="text" id="title" name="title" placeholder="Ex: Panne de connexion..." value="<?php echo htmlspecialchars($title); ?>">
                    <span class="error-msg"><?php echo $title_err; ?></span>
                </div>

                <div class="form-group <?php echo (!empty($description_err)) ? 'has-error' : ''; ?>">
                    <label for="description">Description détaillée</label>
                    <textarea id="description" name="description" rows="6" placeholder="Expliquez ici les détails..."><?php echo htmlspecialchars($description); ?></textarea>
                    <span class="error-msg"><?php echo $description_err; ?></span>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Soumettre la plainte</button>
                    <a href="complaints.php" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include "includes/footer.php"; ?>
</body>
</html>