<?php
$stmt=$connection
->prepare('CALL Pro_Update_Role(?,?,?)');

$stmt->bind_param('iss',$role_id,$role_name,$status);
$role_id=1;
$role_name='staff';
$status='Active';
if($stmt->execute()){
    echo "Role Updated Successfully";
}else{
    echo "Error: ".$stmt->error;
}
?>

