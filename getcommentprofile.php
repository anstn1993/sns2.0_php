<?php
include("connectdatabase.php");//데이터베이스와 연결
$account = $_POST['account'];

$sql="
  SELECT*FROM user
  WHERE account='{$account}'
";

$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($result);

echo $row['image'];

 ?>
