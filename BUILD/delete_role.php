<?php
$stmt = $connection->prepare('CALL Pro_Delete_Role(?)');

$role_id = 10; // It's best practice to define the variable before binding it
$stmt->bind_param('i', $role_id);

if ($stmt->execute()) {
    // Check if any rows were actually deleted
    if ($stmt->affected_rows > 0) {
        echo "Deleted Role successfully.";
    } else {
        echo "No role found with that ID, so nothing was deleted.";
    }
} else {
    echo "Error executing query: " . $stmt->error;
}
?>