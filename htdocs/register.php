<?php
// register.php
session_start();
require_once "config/database.php"; // Connexion PDO

// Initialisation des variables
$name = $email = $password = $confirm_password = "";
$name_err = $email_err = $password_err = $confirm_password_err = "";

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ====== Validation du nom ======
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter your name.";
    } else {
        $name = trim($_POST["name"]);
    }

    // ====== Validation de l'email ======
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        // Vérifier si email existe déjà
        $sql = "SELECT id FROM users WHERE email = :email";
        try {
            if ($stmt = $pdo->prepare($sql)) {
                $stmt->bindParam(":email", $_POST["email"], PDO::PARAM_STR);
                $stmt->execute();
                if ($stmt->rowCount() == 1) {
                    $email_err = "This email is already registered.";
                } else {
                    $email = trim($_POST["email"]);
                }
            }
        } catch (PDOException $e) {
            $email_err = "Erreur de base de données : " . $e->getMessage();
        }
    }

    // ====== Validation du mot de passe ======
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // ====== Validation confirmation mot de passe ======
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if ($password != $confirm_password) {
            $confirm_password_err = "Passwords do not match.";
        }
    }

    // ====== Insertion dans la base si pas d'erreurs ======
    if (empty($name_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)) {
        $sql = "INSERT INTO users (fullname, email, password, role) VALUES (:fullname, :email, :password, 'user')";
        try {
            if ($stmt = $pdo->prepare($sql)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt->bindParam(":fullname", $name, PDO::PARAM_STR);
                $stmt->bindParam(":email", $email, PDO::PARAM_STR);
                $stmt->bindParam(":password", $hashed_password, PDO::PARAM_STR);
                if ($stmt->execute()) {
                    // Rediriger vers login après succès
                    header("Location: login.php");
                    exit();
                } else {
                    echo "erreur";
                }
            }
        } catch (PDOException $e) {
            echo "Erreur lors de l'inscription : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create an account | Modern UI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">✨</div>
            <h2>Inscription</h2>
            <p>Join us to submit your requests.</p>
        </div>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="styled-form">
            <div class="form-group <?php echo (!empty($name_err)) ? 'has-error' : ''; ?>">
                <label>Nom complet</label>
                <input type="text" name="name" placeholder="Jean Dupont" value="<?php echo htmlspecialchars($name); ?>">
                <span class="error-msg"><?php echo $name_err; ?></span>
            </div>

            <div class="form-group <?php echo (!empty($email_err)) ? 'has-error' : ''; ?>">
                <label>Adresse Email</label>
                <input type="email" name="email" placeholder="jean@exemple.fr" value="<?php echo htmlspecialchars($email); ?>">
                <span class="error-msg"><?php echo $email_err; ?></span>
            </div>    

            <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label>Mot de passe</label>
                <input type="password" name="password" placeholder="Minimum 6 caractères">
                <span class="error-msg"><?php echo $password_err; ?></span>
            </div>

            <div class="form-group <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
                <label>Confirm your password</label>
                <input type="password" name="confirm_password" placeholder="Répétez le mot de passe">
                <span class="error-msg"><?php echo $confirm_password_err; ?></span>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Create my account</button>
            
            <div class="auth-footer">
                <p>Already Registered ? <a href="login.php">Log in here</a></p>
            </div>
        </form>
    </div>
</div>

</body>
</html>