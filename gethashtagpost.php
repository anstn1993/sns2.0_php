<?php
include("connectdatabase.php");//데이터베이스와 연결
$jsonData = json_decode(file_get_contents("php://input"), true);
$requestType = $jsonData['requestType'];
//클라이언트에서 넘어온 로그인한 사용자 계정
$account = $jsonData['account'];
//해시태그(#이 붙지 않은 상태다)
$hashTag = $jsonData['hashTag'];


//게시물이 내가 업로드한 게시물인지 아닌지를 판별하는 변수
$isMyPost = true;
//해당 게시물에 사용자가 좋아요를 누른 상태인지 아닌지를 판별하기 위한 변수
$isLike = false;


//페이징 시 한번에 보여줄 게시물의 개수
$list_size = 0;


$sql = "
SELECT*FROM hashtag
WHERE tag='{$hashTag}'
";

$result = mysqli_query($conn, $sql);
$totalCount = mysqli_num_rows($result);

//현재 로드된 마지막 게시물의 번호
$currentLastId = (int)$jsonData['lastId'];

$list_size = (int)$jsonData['listSize'];
$start_index = 0;

$start_index = $jsonData['lastId'];

$hashTag = '#' . $hashTag;

//시작 엔덱스로부터 15개씩 보여준다.
$sql = "
  SELECT*FROM post
  WHERE article like '%{$hashTag}%'
  ORDER BY id DESC LIMIT {$start_index}, {$list_size}
";

$result = mysqli_query($conn, $sql);
//post테이블의 row수를 구한다.
$size = mysqli_num_rows($result);

//post테이블에 데이터가 있는 경우에만 다음의 동작을 수행
if ($size > 0) {
    $data = array();
    while ($row = mysqli_fetch_array($result)) {
        //넘어온 해시태그와 정확히 일치하는 해시태그가 게시글에 있는지 판별하는 변수
        //이 변수가 true로 바뀔 때만 데이터가 넘어가게 된다.
        $hasSameTag = fasle;
        //게시글의 해시태그를 담을 배열
        $hashTagArray = array();
        //#을 기준으로 게시글을 모두 쪼개준다.
        $firstSplit = explode('#', $row['article']);
        //위의 과정까지 하면 여전히 게시글이 해시태그 뒤에 붙어있는 상태이기 때문에 공백을 기준으로 한번 더 쪼개준다.
        for ($i = 1; $i < count($firstSplit); $i++) {
            //'#해시태그\n(\r, \r\n)문자열' 같은 경우에는 개행된 문자까지 태그에 포함되는 문자로 간주하기 때문에 이를 해결하기 위해서
            //개행을 기준으로 문자열을 split해서 배열화하고 그 중 0번째 index만 진짜 태그로 가져간다.
            $non_enter_tag = preg_split('/\r\n|\r|\n/', $firstSplit[$i]);
            //공백을 기준으로 한번 더 쪼개면 쪼개진 문자열 중 가장 첫번째 문자열이 해시태그가 된다.
            $secondSplit = explode(' ', $non_enter_tag[0]);
            //태그를 치고 다음 줄로 넘어가면 개행문자가 추가되는데 이게 같이 있으면 다음 줄로 넘어가지 않고 업로드한 태그와 다른 문자로 인식하기 때문에
            //개행문자를 없애준다.
            $tag = preg_replace('/\r\n|\r|\n/', '', $secondSplit[0]);
            //해시태그를 해시태그 배열에 추가해준다.
            array_push($hashTagArray, '#' . $tag);
        }

        //배열의 해시태그 중 넘어온 해시태그와 정확히 일치하는 게 있는지 확인하고 존재하면 hasSameTag를 true로 전환
        for ($i = 0; $i < count($hashTagArray); $i++) {
            if ($hashTag === $hashTagArray[$i]) {
                $hasSameTag = true;
            }
        }
        //넘어온 해시태그와 정확히 일치하는 해시태그가 게시글에 존재하는 경우에만 데이터를 추가해준다.
        if ($hasSameTag === true) {
            //게시물을 업로드한 사람의 닉네임을 구한다.
            $sql = "
        SELECT*FROM user
        WHERE account='{$row['account']}'
      ";
            $result_ = mysqli_query($conn, $sql);
            $row_ = mysqli_fetch_array($result_);
            $nickname = $row_['nickname'];
            $profile = $row_['image'];
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
                    'type'=>$type,
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
                    'isLike' => $isLike,
                    'totalCount' => $totalCount
                ));
        }

    }
    header('Content-Type: application/json; charset=utf8');
    //json_encode()함수는 첫번째 인자로 string이나 array를 넣으면 그 데이터를 json화시켜 string형태로 출력해주는 함수다.
    $json = json_encode(array('requestType'=>$requestType, "post" => $data), JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
    echo $json;
}
