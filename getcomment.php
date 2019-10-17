<?php
include("connectdatabase.php");//데이터베이스와 연결

$jsonData = json_decode(file_get_contents("php://input"), true);
//클라이언트에서 넘어온 로그인한 사용자 계정
$account = $jsonData['account'];
$postNum = (int)$jsonData['postNum'];
$requestType = $jsonData['requestType'];//요청 타입
$isMyComment = true;

$isMyChildComment = true;


//전체 댓글을 조회하는 쿼리문
$sql = "
  SELECT*FROM comment
  WHERE post_id='{$postNum}'
";

$commentResult = mysqli_query($conn, $sql);
//전체 댓글 수
$totalCommentCount = mysqli_num_rows($commentResult);

//현재 로드된 마지막 댓글의 인댁스
$lastCommentId = (int)$jsonData['lastCommentId'];

$start_index = 0;

//댓글을 최초에 로드할 때는 댓글이 없는 상태이기 때문에 currentLastCommendId는 0이 된다.
if ($lastCommentId == 0) {
    //페이징 시 시작 인덱스
    $start_index = 0;
} //하지만 한번 로드된 후 다음 댓글 뭉치를 로드할 때는 마지막 댓글의 아이디를 통해서 현재 댓글이 테이블 상에서 몇번째에 해당하는지를 구해서 그 숫자를 다음 댓글 로드의 시작 index로 설정해준다.
else {
    //클라이언트에서 넘어온 현재 로드된 가장 마지막 댓글의 id보다 작거나 같은 데이터의 수를 구해서
    //그 수를 다음 로드할 데이터의 시작 index로 설정
    $sql = "
      SELECT COUNT(*) FROM comment WHERE id<='{$lastCommentId}' AND post_id='{$postNum}'
    ";

    $commentResult = mysqli_query($conn, $sql);
    $commentData = mysqli_fetch_array($commentResult);
    $start_index = $commentData[0];
}

//페이징 시 한번에 보여줄 댓글의 개수
$list_size = 10;


//시작 엔덱스로부터 10개씩 보여준다.
$sql = "
  select*from comment, (select account, nickname, image from user)user
  where post_id='{$postNum}' and comment.account = user.account
  order by id asc
  LIMIT {$start_index}, {$list_size}
";

$commentResult = mysqli_query($conn, $sql);
//comment테이블의 row수를 구한다.
$size = mysqli_num_rows($commentResult);

//comment테이블에 데이터가 있는 경우에만 다음의 동작을 수행
if ($size > 0) {
    $data = array();
    while ($commentData = mysqli_fetch_array($commentResult)) {
        //댓글을 단 사람의 닉네임을 구한다.
        $commentNickname = $commentData['nickname'];
        $commentProfile = $commentData['image'];
        //클라이언트에서 넘어온 로그인 사용자 아이디와 댓글에 등록된 아이디가 일치하는 경우
        if ($account == $commentData['account']) {
            //나의 게시물인지를 가려주는 boolean변수를 true로
            $isMyComment = true;
        } //일치하지 않는 경우
        else {
            //나의 게시물인지를 가려주는 boolean변수를 false로
            $isMyComment = false;
        }

        $sql = "
      select count(*) as count from childcomment
      where comment_id={$commentData['id']}
    ";
        $childCommentResult = mysqli_query($conn, $sql);
        $countData = mysqli_fetch_array($childCommentResult);
        $childCommentSize = $countData['count'];
        //해당 댓글에 담긴 대댓글을 조회하는 쿼리문
        $sql = "
      select*from childcomment, (select account, nickname, image from user)user
      where childcomment.account = user.account and comment_id={$commentData['id']}
      order by id asc
      limit 0, 3
    ";
        $childCommentResult = mysqli_query($conn, $sql);
        $childCommentList = array();//대댓글 데이터를 담을 배열
        //대댓글이 하나도 없는 경우
        if ($childCommentSize == 0) {
            //그냥 댓글 데이터만 넘겨준다.
            array_push($data,
                array(
                    'id' => $commentData['id'],
                    'account' => $commentData['account'],
                    'nickname' => $commentNickname,
                    'profile' => $commentProfile,
                    'comment' => $commentData['comment'],
                    'time' => $commentData['time'],
                    'isMyComment' => $isMyComment,
                    'childCommentCount' => $childCommentSize,
                    'childCommentList' => $childCommentList
                ));
        } //대댓글이 존재하는 경우
        else {
            while ($childCommentData = mysqli_fetch_array($childCommentResult)) {
                $childCommentNickname = $childCommentData['nickname'];
                $childCommentProfile = $childCommentData['image'];
                if ($account == $childCommentData['account']) {
                    $isMyChildComment = true;
                } else {
                    $isMyChildComment = false;
                }

                array_push($childCommentList,
                    array(
                        'id' => $childCommentData['id'],
                        'commentNum' => $commentData['id'],
                        'postNum' => $childCommentData['post_id'],
                        'account' => $childCommentData['account'],
                        'nickname' => $childCommentNickname,
                        'profile' => $childCommentProfile,
                        'comment' => $childCommentData['comment'],
                        'time' => $childCommentData['time'],
                        'isMyChildComment' => $isMyChildComment
                    ));
            }

            //그리고 그걸 댓글 데이터에 함께 넣어서 넘겨준다.
            array_push($data,
                array(
                    'id' => $commentData['id'],
                    'account' => $commentData['account'],
                    'nickname' => $commentNickname,
                    'profile' => $commentProfile,
                    'comment' => $commentData['comment'],
                    'time' => $commentData['time'],
                    'isMyComment' => $isMyComment,
                    'childCommentCount' => $childCommentSize,
                    'childCommentList' => $childCommentList
                ));
        }
    }
}
header('Content-Type: application/json; charset=utf8');
//json_encode()함수는 첫번째 인자로 string이나 array를 넣으면 그 데이터를 json화시켜 string형태로 출력해주는 함수다.
$json = json_encode(array("requestType" => $requestType, 'totalCommentCount' => $totalCommentCount, "comment" => $data), JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
echo $json;


?>
