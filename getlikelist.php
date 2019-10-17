<?php
include("connectdatabase.php");//데이터베이스와 연결

$jsonData = json_decode(file_get_contents("php://input"), true);
//좋아요가 달린 게시물 id
$postNum = (int)$jsonData['postNum'];
//현재 로드된 가장 마지막 좋아요 리스트 id
$currentLastId = (int)$jsonData['lastId'];

$myAccount = $jsonData['myAccount'];
$isFollowing = false;


//한 번 로드될 때 보여줄 데이터 수
$list_size = 10;

//매번 로드될 때 시작 index
$start_index = $currentLastId;

$sql="
SELECT*FROM likepost
WHERE post_id='{$postNum}'
LIMIT {$start_index}, {$list_size}
";
$result = mysqli_query($conn, $sql);
$size = mysqli_num_rows($result);

if($size > 0){
  $data = array();
  while($row=mysqli_fetch_array($result)){

    $account = $row['account'];
    $id = $row['id'];

    //좋아요 리스트의 사용자 정보
    $sql="
    SELECT*FROM user
    WHERE account='{$account}'
    ";

    $result_ = mysqli_query($conn, $sql);
    $row_ = mysqli_fetch_array($result_);

    $profile = $row_['image'];
    $nickname = $row_['nickname'];

    //좋아요 리스트의 사용자 팔로잉 여부
    $sql="
      SELECT*FROM follow
      WHERE following_account='{$myAccount}' AND followed_account='{$account}'
    ";
    $result_ = mysqli_query($conn, $sql);
    $size = mysqli_num_rows($result_);

    if($size == 0){
      $isFollowing = false;
    }
    else {
      $isFollowing = true;
    }

    array_push($data,
      array(
          'id'=>$id,
          'account'=>$account,
          'profile'=>$profile,
          'nickname'=>$nickname,
          'isFollowing'=>$isFollowing
      ));
  }

  header('Content-Type: application/json; charset=utf8');
  //json_encode()함수는 첫번째 인자로 string이나 array를 넣으면 그 데이터를 json화시켜 string형태로 출력해주는 함수다.
  $json=json_encode(array("requestType"=>"getLikeList", "likelist"=>$data), JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE);
  echo $json;
}



 ?>
