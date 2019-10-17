<?php
include("connectdatabase.php");//데이터베이스와 연결

$jsonData = json_decode(file_get_contents("php://input"), true);
$childCommentNum = $jsonData['childCommentNum'];
$childCommentPosition = $jsonData['childCommentPosition'];
$commentPosition = $jsonData['commentPosition'];


//답글을 삭제하는 쿼리문
$sql = "
DELETE FROM childcomment
WHERE id={$childCommentNum}
";

$result = mysqli_query($conn, $sql);

if ($result === true) {
    $data = array(
        'requestType' => 'deleteChildComment',
        'childCommentPosition' => (int)$childCommentPosition,
        'commentPosition' => (int)$commentPosition
    );
    header("Content-Type: application/json: charset = utf-8");
    $json = json_encode($data, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
    echo $json;
}
?>
