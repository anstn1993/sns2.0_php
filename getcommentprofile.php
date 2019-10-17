<?php
$account = $_POST['account'];

$host = 'localhost';
$username = 'moonsoo'; # MySQL 계정 아이디
$userpassword = 'Rla933466r!'; # MySQL 계정 패스워드
$dbname = 'SNS';  # DATABASE 이름


//데이터베이스와 연결
$conn = mysqli_connect($host, $username, $userpassword, $dbname);

$sql="
  SELECT*FROM user
  WHERE account='{$account}'
";

$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($result);

echo $row['image'];

 ?>
