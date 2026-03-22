<?php
// login.php
session_start();
require_once "config/database.php";

// Variables
$email = $password = "";
$email_err = $password_err = $login_err = "";

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ====== Validation de l'email ======
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // ====== Validation du mot de passe ======
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // ====== Vérification si pas d'erreurs ======
    if (empty($email_err) && empty($password_err)) {
        $sql = "SELECT id, name, email, password, role FROM users WHERE email = :email";
        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($password, $user['password'])) {
                    // Mot de passe correct -> créer session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['role'] = $user['role'];

                    // Redirection selon rôle
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $login_err = "Invalid email or password.";
                }
            } else {
                $login_err = "Invalid email or password.";
            }
        } else {
            echo "Something went wrong. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | Gestion des Plaintes</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">🚀</div>
            <h2>Bon retour !</h2>
            <p>Connectez-vous pour gérer vos plaintes.</p>
        </div>

        <?php if (!empty($login_err)): ?>
            <div class="alert alert-danger">
                <?php echo $login_err; ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="styled-form">
            <div class="form-group <?php echo (!empty($email_err)) ? 'has-error' : ''; ?>">
                <label>Adresse Email</label>
                <input type="email" name="email" placeholder="nom@exemple.com" value="<?php echo htmlspecialchars($email); ?>">
                <span class="error-msg"><?php echo $email_err; ?></span>
            </div>    

            <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label>Mot de passe</label>
                <input type="password" name="password" placeholder="••••••••">
                <span class="error-msg"><?php echo $password_err; ?></span>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Se connecter</button>
            
            <div class="auth-footer">
                <p>Vous n'avez pas de compte ? <a href="register.php">S'inscrire</a></p>
            </div>
        </form>
    </div>
</div>

</body>
</html>