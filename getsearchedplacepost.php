<?php
include("connectdatabase.php");//데이터베이스와 연결
$jsonData = json_decode(file_get_contents("php://input"), true);
$requestType = $jsonData['requestType'];//요청 타입
$place = $jsonData['address'];//검색된 장소 명
$myAccount = $jsonData['account'];//한 번 로드될 때 보여줄 데이터 수
$list_size = (int)$jsonData['listSize'];//한 번 로드될 때 보여줄 데이터 수
$start_index = (int)$jsonData['lastId'];//리스트의 시작 index

//시작 엔덱스로부터 10개씩 보여준다.
$sql = "
  SELECT*FROM post, (select account, nickname, image from user)user 
  WHERE post.address='{$place}' and post.account = user.account
  ORDER BY id DESC LIMIT {$start_index}, {$list_size}
";

$result = mysqli_query($conn, $sql);
//post테이블의 row수를 구한다.
$size = mysqli_num_rows($result);

//post테이블에 데이터가 있는 경우에만 다음의 동작을 수행
if ($size > 0) {
    $data = array();
    while ($row = mysqli_fetch_array($result)) {

        $nickname = $row['nickname'];
        $profile = $row['image'];
        //클라이언트에서 넘어온 로그인 사용자 아이디와 게시물에 등록된 아이디가 일치하는 경우
        if ($myAccount == $row['account']) {
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
      WHERE post_id='{$row['id']}' AND account='{$myAccount}'
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
                'type'=>$type,
                'account' => $row['account'],
                'nickname' => $nickname,
                'profile' => $profile,
                'article' => $row['article'],
                'video'=>$row['video'],
                'imageList' => $imageList,
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
$json = json_encode(array("requestType" => $requestType, "post" => $data), JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
echo $json;


?>
