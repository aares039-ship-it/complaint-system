<?php
// dashboard.php
require_once "../config/database.php";
require_once "../includes/auth.php"; // protège la page si non connecté

$my_complaint_id = $_GET['complaint_id'];
$role = $_SESSION['role'];

// Initialisation variables statistiques
    $total_complaints = $pending_complaints = $resolved_complaints = 0;

    // Total complaints
    $stmt = $pdo->query("SELECT COUNT(*) FROM complaints");
    $total_complaints = $stmt->fetchColumn();

    // Pending complaints
    $stmt = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'pending'");
    $pending_complaints = $stmt->fetchColumn();

    // Resolved complaints
    $stmt = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'resolved'");
    $resolved_complaints = $stmt->fetchColumn();

// Optionally fetch latest complaints (5 latest)
    $stmt = $pdo->prepare("SELECT * FROM complaints WHERE id = :compid");
    $stmt->bindParam(':compid', $my_complaint_id, PDO::PARAM_INT);
    $stmt->execute();
    $complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | View Complaint</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<?php include "../includes/header.php"; ?>

<main class="main-content">
    <div class="container">
        <header class="dashboard-header">
            <div>
                <h1>Nice to see you again, <?php echo htmlspecialchars($_SESSION['user_name']); ?> 👋</h1>
                <p class="text-muted">here is a summary of the activity on your account .</p>
            </div>
        </header>
        <?php foreach($complaints as $complaint): 
            $complaint_id = $complaint["id"];
            ?>
        <div class="cpmplaint-container ">
            <th class="text-muted">Title</th>
            <h3><?php echo $complaint["title"]?></h3><br>

            <th class="text-muted">Description</th>
            <p><?php echo $complaint["description"]?></p><br>

            <p class="text-muted">Status</p>
            <p><?php echo $complaint["status"]?></p><br>

            <p class="text-muted">Created At</p>
            <p><?php echo $complaint["created_at"]?></p><br>

            <?php if($complaint["status"] == "pending"){   
            ?>
            <a onclick="change_status('inprogress', <?php echo $complaint_id?>)" type="button" class="btn btn-success">Approve</a>
            <a onclick="change_status('rejected' , <?php echo $complaint_id?>)" type="button" class="btn btn-danger">Reject</a>
            <a onclick="change_status('resolved', <?php echo $complaint_id?>)" type="button" class="btn btn-bisecondary">Solved</a>
            <?php
            }elseif($complaint["status"] == "inprogress"){
            ?>
            <a onclick="change_status('resolved', <?php echo $complaint_id?>)" type="button" class="btn btn-bisecondary">Solved</a>
            <?php
            }
            ?>
            
            
        </div>


        <?php endforeach; ?>
    </div>
</main>

<?php include "../includes/footer.php"; ?>

<script type="text/javascript">
  function change_status(value_to, complaint_id)
  {
    var action = "change_stauts";

       var xhttp = new XMLHttpRequest();
       xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {

          var msg = this.responseText;
          var ms = msg.trim();




            if (ms=='success') 
            {
               alert("Operation succeeded!");
               window.location = 'dashboard.php';
            }
            else
            {
              alert("Operation failed!")
              window.location = 'dashboard.php';
            }
            
          }
        };
   
        xhttp.open("POST", "changeStatus.php", true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("action=" + action + "&mvalue_to=" + value_to+ "&mcomplaint_id="+complaint_id);  
  }
    
</script>


</body>
</html>