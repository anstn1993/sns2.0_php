<?php
include("connectdatabase.php");//데이터베이스와 연결

$jsonData = json_decode(file_get_contents("php://input"), true);
$account = $jsonData['account'];

//로그인상태를 로그아웃(0)으로 만들고 토큰을 지워준다.
$sql="
  UPDATE user SET
  login=0,
  token=NULL
  WHERE account='{$account}'
";
$result=mysqli_query($conn, $sql);
$data = array(
    'requestType'=>'logOut'
);
header("Content-Type: application/json: charset = utf-8");
$json = json_encode($data, JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE);
echo $json;
 ?>
