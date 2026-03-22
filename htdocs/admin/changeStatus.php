<?php
require_once "../config/database.php";

$complaint_id = $_POST["mcomplaint_id"];
$value_to = $_POST["mvalue_to"];


try {
    $sql = "UPDATE complaints SET status = :status WHERE id = :id";
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':status' => $value_to,
        ':id'    => $complaint_id
    ]);

    if ($stmt->rowCount() > 0) {
        echo "success";
    } else {
        echo "failed";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>