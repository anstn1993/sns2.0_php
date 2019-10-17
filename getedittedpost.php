<?php
//클라이언트에서 넘어온 게시물 번호
$postNum = $_POST['postNum'];
//게시물에 좋아요를 누른 상태인지 판별하는 변수
$isLike = false;


$host = 'localhost';
$username = 'moonsoo'; # MySQL 계정 아이디
$userpassword = 'Rla933466r!'; # MySQL 계정 패스워드
$dbname = 'SNS';  # DATABASE 이름

//데이터베이스와 연결
$conn = mysqli_connect($host, $username, $userpassword, $dbname);

$sql = "
  SELECT*FROM post
  WHERE id={$postNum}
";

$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($result);

//수정된 게시물의 닉네임을 구한다.
$sql = "
  SELECT*FROM user
  WHERE account='{$row['account']}'
";
$result_ = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($result_);
$nickname = $row['nickname'];
$profile = $row['image'];

//수정된 게시물의 댓글 수를 구한다.
$sql = "
  SELECT*FROM comment
  WHERE post_id='{$row['id']}'
";
$result_ = mysqli_query($conn, $sql);
$commentNum = mysqli_num_rows($result_);

//해당 게시물에 달린 좋아요 조회
$sql = "
  SELECT*FROM likepost
  WHERE post_id='{$row['id']}'
";
$result_ = mysqli_query($conn, $sql);
$likeCount = mysqli_num_rows($result_);

//해당 게시물에 좋아요를 했는지 조회
$sql = "
  SELECT*FROM likepost
  WHERE post_id='{$row['id']}' AND account='{$row['id']}'
";
$result_ = mysqli_query($conn, $sql);
$count = mysqli_num_rows($result_);
//만약 해당 게시물에 좋아요를 하지 않은 경우
if($count == 0){
  $isLike = false;
}
//해당 게시물에 좋아요를 한 경우
else{
  $isLike = true;
}

$data = array(
  'account'=>$row['account'],
  'nickname'=>$nickname,
  'profile'=>$profile,
  'article'=>$row['article'],
  'image1'=>$row['image1'],
  'image2'=>$row['image2'],
  'image3'=>$row['image3'],
  'image4'=>$row['image4'],
  'image5'=>$row['image5'],
  'image6'=>$row['image6'],
  'time'=>$row['time'],
  'address'=>$row['address'],
  'latitude'=>$row['latitude'],
  'longitude'=>$row['longitude'],
  'isMyPost'=>true,
  'postNum'=>$postNum,
  'commentCount'=>$commentNum,
  'likeCount'=>$likeCount,
  'isLike'=>$isLike
);

  header('Content-Type: application/json; charset=utf8');
  //json_encode()함수는 첫번째 인자로 string이나 array를 넣으면 그 데이터를 json화시켜 string형태로 출력해주는 함수다.
  $json=json_encode($data, JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE);
  echo $json;




 ?>
