<?php
include("connectdatabase.php");//데이터베이스와 연결

$jsonData = json_decode(file_get_contents("php://input"), true);//json string -> json Object

$commentNum = $jsonData['commentNum'];
$position = $jsonData['position'];

$host = 'localhost';
$username = 'moonsoo'; # MySQL 계정 아이디
$userpassword = 'Rla933466r!'; # MySQL 계정 패스워드
$dbname = 'SNS';  # DATABASE 이름

//데이터베이스와 연결
$conn = mysqli_connect($host, $username, $userpassword, $dbname);

//댓글에 딸린 대댓글을 먼저 지워주고
$sql = "
DELETE FROM childcomment
WHERE comment_id='{$commentNum}'
";

$result = mysqli_query($conn, $sql);

//댓글을 지워준다.
$sql = "
DELETE FROM comment
WHERE id='{$commentNum}'
";

$result = mysqli_query($conn, $sql);

if($result === true){
    $data = array(
        'requestType'=>'deleteComment',
        'position'=>$position
    );
    header('Content-Type: application/json; charset=utf8');
    //json_encode()함수는 첫번째 인자로 string이나 array를 넣으면 그 데이터를 json화시켜 string형태로 출력해주는 함수다.
    $json = json_encode($data, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
    echo $json;

}

 ?>
