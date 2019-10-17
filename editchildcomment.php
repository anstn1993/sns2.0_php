<?php
include("connectdatabase.php");//데이터베이스와 연결

$jsonData = json_decode(file_get_contents("php://input"), true);
//수정된 답글
$edittedChildComment = $jsonData['edittedChildComment'];
//수정하려는 답글의 번호
$childCommentNum = $jsonData['childCommentNum'];

$host = 'localhost';
$username = 'moonsoo'; # MySQL 계정 아이디
$userpassword = 'Rla933466r!'; # MySQL 계정 패스워드
$dbname = 'SNS';  # DATABASE 이름

//데이터베이스와 연결
$conn = mysqli_connect($host, $username, $userpassword, $dbname);

//답글 수정 쿼리문
$sql = "
UPDATE childcomment
SET
comment='{$edittedChildComment}'
WHERE id='{$childCommentNum}'
";

$result = mysqli_query($conn, $sql);

//쿼리문이 잘 실행됐으면
if($result===true){
    $data = array(
        'requestType'=>'editChildComment',
        'childComment'=>$edittedChildComment
    );
    //클라이언트로 수정된 댓글을 돌려준다.
    header('Content-Type: application/json; charset=utf8');
    //json_encode()함수는 첫번째 인자로 string이나 array를 넣으면 그 데이터를 json화시켜 string형태로 출력해주는 함수다.
    $json=json_encode($data, JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE);
  echo $json;
}
 ?>
