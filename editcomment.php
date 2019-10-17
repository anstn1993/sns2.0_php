<?php
include("connectdatabase.php");//데이터베이스와 연결
$jsonData = json_decode(file_get_contents("php://input"), true);
$comment = $jsonData['comment'];
$commentNum = (int)$jsonData['commentNum'];

//댓글 수정 쿼리문
$sql = "
UPDATE comment
SET
comment='{$comment}'
WHERE id='{$commentNum}'
";

$result = mysqli_query($conn, $sql);

//댓글이 잘 수정된 경우
if ($result === true) {
    $data = array(
        'requestType' => 'editComment',
        'comment' => $comment,
    );
    header('Content-Type: application/json; charset=utf8');
    //json_encode()함수는 첫번째 인자로 string이나 array를 넣으면 그 데이터를 json화시켜 string형태로 출력해주는 함수다.
    $json = json_encode($data, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
    echo $json;
}

?>
