<?php
$host = 'localhost';
$user_name = 'user name'; # MySQL 계정 아이디
$user_password = 'user password'; # MySQL 계정 패스워드
$dbname = 'database name';  # DATABASE 이름

//데이터베이스와 연결
$conn = mysqli_connect($host, $username, $userpassword, $dbname);
?>
