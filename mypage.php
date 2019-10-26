<?php
include("connectdatabase.php");//데이터베이스와 연결
//게시물 페이지의 주인 account
$userAccount=$_GET['userAccount'];
//로그인한 사용자의 account
$myAccount = $_GET['myAccount'];
//로그인한 사용자가 게시물 페이지의 주인을 follow하고 있는지 여부
$isFollowing = false;



//사용자에게 넘겨받은 아이디로 user테이블 조회
$sql="
  SELECT*FROM user
  WHERE account='{$userAccount}'
";
//쿼리 조회
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($result);



$name=$row['name'];
$nickname=$row['nickname'];
$email=$row['email'];
$introduce=$row['introduce'];
$imagename=$row['image'];

//사용자의 게시물 수 조회
$sql="
  SELECT*FROM post
  WHERE account='{$userAccount}'
";
//쿼리 조회
$result = mysqli_query($conn, $sql);
$postCount = mysqli_num_rows($result);

//사용자의 팔로워 수 조회(사용자를 팔로우하고 있는 사람의 수)
$sql="
  SELECT*FROM follow
  WHERE followed_account='{$userAccount}'
";
//쿼리 조회
$result = mysqli_query($conn, $sql);
$followerCount = mysqli_num_rows($result);

//사용자의 팔로잉 수 조회(사용자가 팔로우하고 있는 사람의 수)
$sql="
  SELECT*FROM follow
  WHERE following_account='{$userAccount}'
";
//쿼리 조회
$result = mysqli_query($conn, $sql);
$followingCount = mysqli_num_rows($result);

//해당 사용자를 팔로우하고 있는지 여부(내가 팔로우하고 사용자가 팔로우 당하는 관계)
$sql="
  SELECT*FROM follow
  WHERE following_account='{$myAccount}' AND followed_account='{$userAccount}'
";
//쿼리 조회
$result = mysqli_query($conn, $sql);
$size = mysqli_num_rows($result);

//
if($size == 0){
  $isFollowing = false;
}
else {
  $isFollowing = true;
}

//사용자 세션 데이터를 배열에 담은 후
$user_data = array(
  'account'=>$userAccount,
  'name'=>$name,
  'nickname'=>$nickname,
  'email'=>$email,
  'introduce'=>$introduce,
  'image'=>$imagename,
  'postCount'=>$postCount,
  'followerCount'=>$followerCount,
  'followingCount'=>$followingCount,
  'isFollowing'=>$isFollowing);
//json string으로 인코딩한 값을 클라이언트에 보내주기 위한 echo 설정
echo json_encode($user_data, JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE);



 ?>
