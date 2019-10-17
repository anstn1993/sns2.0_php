<?php
include("connectdatabase.php");//데이터베이스와 연결

$jsonData = json_decode(file_get_contents("php://input"), true);

$requestType = $jsonData['requestType'];//요청 타입
$account = $jsonData['account'];//클라이언트에서 넘어온 로그인한 사용자 계정
$lastPostNum = $jsonData['lastPostNum'];//현재 로드된 게시물 중 마지막 게시물 번호
//게시물이 내가 업로드한 게시물인지 아닌지를 판별하는 변수
$isMyPost = true;
//해당 게시물에 사용자가 좋아요를 누른 상태인지 아닌지를 판별하기 위한 변수
$isLike = false;

//페이징 시 한번에 보여줄 게시물의 개수
$list_size = $jsonData['postCount'];

//페이징 시 시작 인덱스
$start_index;
if ($lastPostNum == 0) {
    $start_index = 0;
} else {
//현재 로드된 게시물의 개수를 구하기 위한 sql
    $sql = "
    SELECT COUNT(*) AS count FROM post WHERE id>='{$lastPostNum}'
";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($result);
    $start_index = $row['count'];
}

//시작 엔덱스로부터 10개씩 보여준다.
$sql = "
  select*from post, (select account, nickname, image from user)user 
  where post.account = user.account
  ORDER BY id DESC LIMIT {$start_index}, {$list_size}
";

$result = mysqli_query($conn, $sql);
//post테이블의 row수를 구한다.
$size = mysqli_num_rows($result);

$data = array();//클라이언트로 respose해줄 json 스트링을 만들기 위한 배열 선언
//post테이블에 데이터가 있는 경우에만 다음의 동작을 수행
if ($size > 0) {

    while ($row = mysqli_fetch_array($result)) {

        $nickname = $row['nickname'];
        $profile = $row['image'];
        //클라이언트에서 넘어온 로그인 사용자 아이디와 게시물에 등록된 아이디가 일치하는 경우
        if ($account == $row['account']) {
            //나의 게시물인지를 가려주는 boolean변수를 true로
            $isMyPost = true;
        } //일치하지 않는 경우
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
        if ($count == 0) {
            $isLike = false;
        } //해당 게시물에 좋아요를 한 경우
        else {
            $isLike = true;
        }

        $type = "image";//게시물 타입
        //게시물이 비디오 타입인 경우
        if (!empty($row['video']) || $row['video'] != "") {
            $type = "video";
        }
        $imageList = array($row['image1'], $row['image2'], $row['image3'], $row['image4'], $row['image5'], $row['image6']);
        foreach ($imageList as $key => $image) {
            if (empty($imageList[$key]) || $imageList[$key] == "") {
                unset($imageList[$key]);
            }
        }

        array_push($data,
            array(
                'type' => $type,
                'account' => $row['account'],
                'nickname' => $nickname,
                'profile' => $profile,
                'article' => $row['article'],
                'imageList' => $imageList,
                'video'=>$row['video'],
                'time' => $row['time'],
                'address' => $row['address'],
                'latitude' => $row['latitude'],
                'longitude' => $row['longitude'],
                'isMyPost' => $isMyPost,
                'postNum' => $row['id'],
                'commentCount' => $commentCount,
                'likeCount' => $likeCount,
                'isLike' => $isLike
            ));
    }

}
header('Content-Type: application/json; charset=utf8');
//json_encode()함수는 첫번째 인자로 string이나 array를 넣으면 그 데이터를 json화시켜 string형태로 출력해주는 함수다.
$json = json_encode(array('requestType' => $requestType, 'post' => $data), JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
echo $json;


?>
