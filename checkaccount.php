<?php
$host = 'localhost';
$username = 'moonsoo'; # MySQL 계정 아이디
$password = 'Rla933466r!'; # MySQL 계정 패스워드
$dbname = 'SNS';  # DATABASE 이름


//클라이언트에서 넘어온 회원가입란에 입력한 id
$account=$_POST['account'];

$conn = mysqli_connect($host, $username, $password, $dbname);
$sql="
    SELECT*FROM user
    WHERE account='{$account}'
    ";

$result= mysqli_query($conn, $sql);

$row_number=mysqli_num_rows($result);

//클라이언트에서 넘겨받은 아이디값으로 쿼리문을 넣었을 때 row가 0이면 아직 그 아이디는 테이블에 없는 것
if($row_number==0){
  echo "사용 가능한 아이디 입니다.";
}else{
  echo "이미 사용 중인 아이디 입니다.";
}



 ?>
