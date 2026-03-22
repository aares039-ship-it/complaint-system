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
        $sql = "SELECT id, fullname, email, password, role FROM users WHERE email = :email";
        try {
            if ($stmt = $pdo->prepare($sql)) {
                $stmt->bindParam(":email", $email, PDO::PARAM_STR);
                $stmt->execute();

                if ($stmt->rowCount() == 1) {
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (password_verify($password, $user['password'])) {
                        // Mot de passe correct -> créer session
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['fullname'];
                        $_SESSION['role'] = $user['role'];

                        $user_role = $user['role'];

                        // Redirection selon rôle

                        if($user_role == "admin"){
                            header("Location: admin/dashboard.php");
                        }
                        elseif($user_role == "user"){
                            header("Location: dashboard.php");
                        }
                        exit();
                    } else {
                        $login_err = "Invalid email or password.";
                    }
                } else {
                    $login_err = "Invalid email or password.";
                }
            }
        } catch (PDOException $e) {
            $login_err = "Erreur de base de données : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | complaint managements</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">🚀</div>
            <h2>Welcome back!</h2>
            <p>Log in to manage your complaints.</p>
        </div>

        <?php if (!empty($login_err)): ?>
            <div class="alert alert-danger">
                <?php echo $login_err; ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="styled-form">
            <div class="form-group <?php echo (!empty($email_err)) ? 'has-error' : ''; ?>">
                <label>Address Email</label>
                <input type="email" name="email" placeholder="nom@exemple.com" value="<?php echo htmlspecialchars($email); ?>">
                <span class="error-msg"><?php echo $email_err; ?></span>
            </div>    

            <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••">
                <span class="error-msg"><?php echo $password_err; ?></span>
            </div>

            <button type="submit" class="btn btn-primary btn-full">connect</button>
            
            <div class="auth-footer">
                <p>you don't have an account? <a href="register.php">Sign Up</a></p>
            </div>
        </form>
    </div>
</div>

</body>
</html>