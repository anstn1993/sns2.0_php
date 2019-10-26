<?php
include("connectdatabase.php");//데이터베이스와 연결
//클라이언트에서 넘어온 로그인한 사용자 계정
$account = $_POST['account'];
//게시물이 내가 업로드한 게시물인지 아닌지를 판별하는 변수
$isMyPost = true;
//해당 게시물에 사용자가 좋아요를 누른 상태인지 아닌지를 판별하기 위한 변수
$isLike = false;



//페이징 시 한번에 보여줄 게시물의 개수
$list_size = 0;

$sql = "
SELECT*FROM post
WHERE account='{$account}'
";

$result = mysqli_query($conn, $sql);
$totalCount = mysqli_num_rows($result);

//현재 로드된 마지막 댓글의 인댁스
$currentId = (int)$_POST['currentId'];

//수정된 게시물의 데이터를 조회하는 쿼리문
$sql = "
  SELECT*FROM post
  WHERE account='{$account}' AND id='{$currentId}'
";

$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($result);

  $data = array();



    //게시물을 업로드한 사람의 닉네임을 구한다.
    $sql = "
      SELECT*FROM user
      WHERE account='{$row['account']}'
    ";
    $result_ = mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($result_);
    $nickname = $row['nickname'];
    $profile = $row['image'];
    //클라이언트에서 넘어온 로그인 사용자 아이디와 게시물에 등록된 아이디가 일치하는 경우
    if($account == $row['account']){
      //나의 게시물인지를 가려주는 boolean변수를 true로
      $isMyPost = true;
    }
    //일치하지 않는 경우
    else {
      //나의 게시물인지를 가려주는 boolean변수를 false로
      $isMyPost = false;
    }
    //해당 게시물에 달린 댓글 조회
    $sql = "
      SELECT*FROM comment
      WHERE post_id='{$row['id']}'
    ";
    $result_ = mysqli_query($conn, $sql);
    $commentCount = mysqli_num_rows($result_);

    //해당 게시물에 달린 대댓글 조회
    $sql = "
      SELECT*FROM childcomment
      WHERE post_id='{$row['id']}'
    ";
    $result_ = mysqli_query($conn, $sql);
    $commentCount += mysqli_num_rows($result_);

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
      WHERE post_id='{$row['id']}' AND account='{$account}'
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


    array_push($data,
      array(
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
        'isMyPost'=>$isMyPost,
        'postNum'=>$row['id'],
        'commentCount'=>$commentCount,
        'likeCount'=>$likeCount,
        'isLike'=>$isLike,
        'totalCount'=>$totalCount
      ));

  header('Content-Type: application/json; charset=utf8');
  //json_encode()함수는 첫번째 인자로 string이나 array를 넣으면 그 데이터를 json화시켜 string형태로 출력해주는 함수다.
  $json=json_encode(array("post"=>$data), JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE);
  echo $json;
 ?>
