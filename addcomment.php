<?php
include("connectdatabase.php");//데이터베이스와 연결

$jsonData = json_decode(file_get_contents("php://input"), true);

$postNum = $jsonData['postNum'];
$account = $jsonData['account'];
$comment = $jsonData['comment'];
$time = date("Y-m-d H:i:s");


$sql = "
INSERT INTO comment(post_id, account, comment, time)
VALUES(
  '$postNum',
  '$account',
  '$comment',
  '$time'
  )
";

$result = mysqli_query($conn, $sql);
//성공적으로 댓글이 테이블에 저장되면
if($result === true){
  $sql = "
  SELECT*FROM comment
  ORDER BY id DESC
  ";
  $result = mysqli_query($conn, $sql);
  $row = mysqli_fetch_array($result);

  $commentNum = $row['id'];


  $sql = "
  SELECT*FROM user
  WHERE account='{$row['account']}'
  ";
  $result = mysqli_query($conn, $sql);
  $row_=mysqli_fetch_array($result);

  $profile = $row_['image'];
  $nickname = $row_['nickname'];

  $data = array(

      'id'=>$commentNum,
      'postNum'=>$postNum,
      'account'=>$row['account'],
      'nickname'=>$nickname,
      'profile'=>$profile,
      'comment'=>$comment,
      'time'=>$time,
      'isMyComment'=>true
  );
  header('Content-Type: application/json; charset=utf8');
  //json_encode()함수는 첫번째 인자로 string이나 array를 넣으면 그 데이터를 json화시켜 string형태로 출력해주는 함수다.
  $json=json_encode(array('requestType'=>'addComment', 'addedComment'=>$data), JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE);
  echo $json;
}

 ?>
