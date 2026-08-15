<?php
$hostname='localhost';
$username='li';
$password='123';
$dbname='inventory_management_system';
$port='3306';
$connection=
new mysqli($hostname,$username,$password,$dbname,$port);
// testing ConnectionDatabase
// if($connection->connect_error)
// {
//     echo "Connection database failed";

// }else{

//     echo "Database Connection Successfully";
// }
?>