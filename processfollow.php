<?php
include("connectdatabase.php");//데이터베이스와 연결

$jsonData = json_decode(file_get_contents("php://input"), true);
$followState = $jsonData['followState'];//팔로우 상태(팔로우를 하는 상태인지 취소하는 상태인지)
$followedAccount = $jsonData['followedAccount'];//팔로우를 당하는 사용자 계정
$followedNickname = $jsonData['followedNickname'];//팔로우 당하는 사용자 닉네임
$followingAccount = $jsonData['followingAccount'];//팔로우를 하는 사용자 계정
$position = $jsonData['position'];//리사이클러뷰 상에서 팔로우를 당한 사용자의 목록이 위치한 index

$sql = "";
//팔로우를 하는 경우
if ($followState === true) {
    $sql = "
  INSERT INTO follow (following_account, followed_account)
  VALUES(
    '$followingAccount',
    '$followedAccount'
    )
  ";

    $result = mysqli_query($conn, $sql);

    if ($result === true) {
        $data = array(
            "requestType"=>"processFollow",
            "isFollowing"=>true,
            "followedNickname"=>$followedNickname,
            "position"=>$position
        );
        $json = json_encode($data, JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE);
        echo $json;
    }
} //좋아요를 취소하는 경우
else {
    $sql = "
    DELETE FROM follow
    WHERE following_account='{$followingAccount}' AND followed_account='{$followedAccount}'
   ";

    $result = mysqli_query($conn, $sql);

    if ($result === true) {
        $data = array(
            "requestType"=>"processFollow",
            "isFollowing"=>false,
            "followedNickname"=>$followedNickname,
            "position"=>$position
        );
        $json = json_encode($data, JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE);
        echo $json;
    }
}


?>
