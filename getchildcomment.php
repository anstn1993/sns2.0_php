<?php

include("connectdatabase.php");//데이터베이스와 연결

$jsonData = json_decode(file_get_contents("php://input"), true);

$account = $jsonData['account'];
//대댓글 개수
$lastChildCommentId = (int)$jsonData['lastChildCommentId'];
//대댓글이 달린 게시물의 번호
$postNum = (int)$jsonData['postNum'];
//대댓글의 부모 댓글의 번호
$commentNum = (int)$jsonData['commentNum'];
//대댓글의 부모 뎃글의 리사이클러뷰 상의 index
$parentPosition = (int)$jsonData['parentPosition'];
//나의 대댓글인지를 가려주는 boolean
$isMyComment = true;

$host = 'localhost';
$username = 'moonsoo'; # MySQL 계정 아이디
$userpassword = 'Rla933466r!'; # MySQL 계정 패스워드
$dbname = 'SNS';  # DATABASE 이름

//데이터베이스와 연결
$conn = mysqli_connect($host, $username, $userpassword, $dbname);
//한번에 보여줄 대댓글 개수
$list_size = 3;

$sql = "
  SELECT COUNT(*) FROM childcomment WHERE id<='{$lastChildCommentId}' AND comment_id='{$commentNum}'
";

$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($result);
//페이징 시 시작 인덱스
$start_index = $row[0];

$sql = "
  SELECT*FROM childcomment
  WHERE comment_id={$commentNum}
  LIMIT {$start_index}, {$list_size}
";

$result = mysqli_query($conn, $sql);
//대댓글 테이블의 row수를 구한다.
$size = mysqli_num_rows($result);
//대댓글이 존재하는 경우
if ($size > 0) {
    $data = array();
    //대댓글이 존재하는 만큼 반복
    while ($row = mysqli_fetch_array($result)) {
        //대댓글을 게시한 사람의 닉네임을 구한다.
        $sql = "
      SELECT*FROM user
      WHERE account='{$row['account']}'
    ";
        $result_ = mysqli_query($conn, $sql);
        $row_ = mysqli_fetch_array($result_);
        $nickname = $row_['nickname'];
        $profile = $row_['image'];
        //클라이언트에서 넘어온 로그인 사용자 아이디와 대댓글에 등록된 아이디가 일치하는 경우
        if ($account == $row['account']) {
            //나의 대댓글인지를 가려주는 boolean변수를 true로
            $isMyComment = true;
        } //일치하지 않는 경우
        else {
            //나의 대댓글인지를 가려주는 boolean변수를 false로
            $isMyComment = false;
        }
        array_push($data,
            array(
                'id' => $row['id'],
                'postNum' => $postNum,
                'commentNum' => $commentNum,
                'account' => $row['account'],
                'nickname' => $nickname,
                'profile' => $profile,
                'comment' => $row['comment'],
                'time' => $row['time'],
                'isMyComment' => $isMyComment
            ));
    }
    header('Content-Type: application/json; charset=utf8');
    //json_encode()함수는 첫번째 인자로 string이나 array를 넣으면 그 데이터를 json화시켜 string형태로 출력해주는 함수다.
    $json = json_encode(array("requestType" => "loadNextChildComment", "parentPosition" => $parentPosition, "childComment" => $data), JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
    echo $json;
}

?>
