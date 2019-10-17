<?php
include("connectdatabase.php");//데이터베이스와 연결
$jsonData = json_decode(file_get_contents("php://input"), true);//json String -> json Object

$likeState = $jsonData['likeState'];
$account = $jsonData['account'];
$postNum = (int)$jsonData['postNum'];
$position = (int)$jsonData['position'];


$sql = "";

//좋아요를 하는 경우
if ($likeState === true) {
    $sql = "
  INSERT INTO likepost (post_id, account)
  VALUES(
    '$postNum',
    '$account'
    )
  ";

    $result = mysqli_query($conn, $sql);

    if ($result === true) {
        header('Content-Type: application/json; charset=utf8');
        $data = array(
            'requestType'=>'likeOk',
            'position'=>$position
        );//채팅 데이터를 추가할 연관배열 선언
        echo json_encode($data, JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE);
    }
} //좋아요를 취소하는 경우
else {
    $sql = "
    DELETE FROM likepost
    WHERE post_id='{$postNum}' AND account='{$account}'
   ";

    $result = mysqli_query($conn, $sql);

    if ($result === true) {
        header('Content-Type: application/json; charset=utf8');
        $data = array(
            'requestType'=>'likeCancel',
            'position'=>$position
        );//채팅 데이터를 추가할 연관배열 선언
        echo json_encode($data, JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE);
    }
}


?>
