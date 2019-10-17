<?php
include("connectdatabase.php");//데이터베이스와 연결

$jsonData = json_decode(file_get_contents("php://input"), true);
$account = $jsonData['account'];
$postNum = $jsonData['postNum'];
$commentNum = $jsonData['commentNum'];
$childComment = $jsonData['childComment'];
$time = date("Y-m-d H:i:s");

//대댓글 데이터를 테이블에 추가하는 쿼리문
$sql = "
INSERT INTO childcomment (post_id, comment_id, account, comment, time)
VALUES (
  '$postNum',
  '$commentNum',
  '$account',
  '$childComment',
  '$time'
  )
";

$result = mysqli_query($conn, $sql);

if ($result === true) {

    $sql = "
    SELECT*FROM user
    WHERE account='{$account}'
  ";

    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($result);
    $profile = $row['image'];
    $nickname = $row['nickname'];

    $sql = "
    SELECT*FROM childcomment
    ORDER BY id DESC
  ";

    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($result);
    $childCommentNum = $row['id'];

    $data = array(
        'id' => $childCommentNum,
        'postNum' => $postNum,
        'commentNum' => $commentNum,
        'account' => $account,
        'nickname' => $nickname,
        'profile' => $profile,
        'comment' => $childComment,
        'time' => $time,
        'isMyComment' => true
    );
    header('Content-Type: application/json; charset=utf8');
    //json_encode()함수는 첫번째 인자로 string이나 array를 넣으면 그 데이터를 json화시켜 string형태로 출력해주는 함수다.
    $json = json_encode(array('requestType' => 'addChildComment', 'addedComment' => $data), JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
    echo $json;
}
?>
