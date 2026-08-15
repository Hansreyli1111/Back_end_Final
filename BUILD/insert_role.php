<?php
$stmt=$connection->prepare('CALL Pro_Insert_Role(?)');
$stmt->bind_param('s',$role_name);
$role_name='Manager';
if($stmt->execute()){
    echo "Inserted Role successfully/<br>";
}else{
    echo "Error inserting role";
}
?>