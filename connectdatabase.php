<?php
$host = 'localhost';
$user_name = 'user name'; # MySQL 계정 아이디(자신의 아이디로 수정해서 쓸 것)
$user_password = 'user password'; # MySQL 계정 패스워드(자신의 비밀번호로 수정해서 쓸 것)
$dbname = 'database name';  # DATABASE 이름(자신의 db이름으로 수정해서 쓸 )

//데이터베이스와 연결
$conn = mysqli_connect($host, $username, $userpassword, $dbname);
?>
