<?php
include("connectdatabase.php");//데이터베이스와 연결
//클라이언트에서 넘어온 회원 정보
$account = $_POST['account'];
$password =hash("sha256",$_POST['password']) ;
$name = $_POST['name'];
$nickname = $_POST['nickname'];
$email = $_POST['email'];



//쿼리문
$sql="
    INSERT INTO user(account, password, name, nickname, email)
    VALUES (
      '$account',
      '$password',
      '$name',
      '$nickname',
      '$email'
    )
    ";
$result=mysqli_query($conn, $sql);
//사용자 세션 데이터를 배열에 담은 후
$user_data = array('account'=>$account, 'name'=>$name, 'nickname'=>$nickname, 'email'=>$email);
//json string으로 인코딩한 값을 클라이언트에 보내주기 위한 echo 설정
echo json_encode($user_data, JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE);



 ?>
