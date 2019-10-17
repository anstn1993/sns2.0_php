<?php
include("connectdatabase.php");//데이터베이스와 연결
$jsonData = json_decode(file_get_contents("php://input"), true);

$requestType = $jsonData['requestType'];//요청 타입
$account = $jsonData['account'];//로그인한 사용자 계정
$lastId = $jsonData['lastId'];//현재 로드된 가장 마지막 리스트 id
$list_size = $jsonData['listSize'];//한 번 로드될 때 보여줄 데이터 수

//리스트의 시작 index
$start_index = 0;

//팔로잉 리스트를 처음 로드할 때는 index를 0으로 해서 처음부터 다 보여준다.
if ($lastId == 0) {
    $start_index = 0;
} //하지만 이후 페이징이 사직되면 로드되어있는 리스트 중 마지막 리스트의 id를 보내주기 때문에 그 값을 통해 마지막 index를 찾는 쿼리를 실행해서 시작 index를 구한다.
else {
    //클라이언트에서 넘어온 현재 로드된 가장 마지막 팔로잉데이터의 id보다 작거나 같은 데이터의 수를 구해서
    //그 수를 다음 로드할 데이터의 시작 index로 설정
    $sql = "
    SELECT COUNT(*) FROM notification WHERE id>='{$lastId}' AND account='{$account}'
  ";

    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($result);
    $start_index = $row[0];
}

//notification 리스트를 담을 배열
$data = array();

$sql = "
  select*from notification, (select account as userAccount, nickname, image from user)user
  where notification.account='{$account}' and notification.user_account = user.userAccount
  ORDER BY id DESC
  LIMIT {$start_index}, {$list_size}
";

$result = mysqli_query($conn, $sql);

$size = mysqli_num_rows($result);

//데이터가 존재하는 경우
if ($size > 0) {
    while ($row = mysqli_fetch_array($result)) {
        //알림 카테고리
        $category = $row['category'];
        //알림 리스트 id
        $id = $row['id'];
        //알림 대상 게시물의 id
        $postNum = $row['post_id'];
        //알림 대상 댓글의 id
        $commentNum = $row['comment_id'];
        //알림 대상 대댓글의 id
        $childcommentNum = $row['childcomment_id'];
        //알림의 대상이 되는 사용자 계정
        $account = $row['account'];
        //알림을 유발한 사용자 계정
        $userAccount = $row['user_account'];
        //알림 시간
        $time = $row['time'];
        //알림을 유발한 사용자의 프로필 사진
        $profile = $row['image'];
        //알림을 유발한 사용자의 닉네임
        $nickname = $row['nickname'];
        //알림의 본문
        $body = $row['body'];

        $sql = "
      SELECT*FROM post
      WHERE id={$postNum}
    ";

        $result_ = mysqli_query($conn, $sql);
        $row_ = mysqli_fetch_array($result_);
        //알림의 대상이 되는 게시물의 대표 이미지
        $image = $row_['image1'];
        //알림의 확인 여부
        $isChecked = false;
        if ($row['is_checked'] == 1) {
            $isChecked = true;
        }

        $sql = "
      SELECT*FROM follow
      WHERE following_account='{$account}' AND followed_account='{$userAccount}'
    ";
        $result_ = mysqli_query($conn, $sql);
        $size = mysqli_num_rows($result_);
        //알림을 유발한 사람을 팔로우하고 있는지 여부
        $isFollowing = false;
        if ($size > 0) {
            $isFollowing = true;
        }

        array_push($data,
            array(
                'id' => $id,
                'postNum' => $postNum,
                'commentNum' => $commentNum,
                'account' => $account,
                'userAccount' => $userAccount,
                'profile' => $profile,
                'nickname' => $nickname,
                'body' => $body,
                'image' => $image,
                'category' => $category,
                'isChecked' => $isChecked,
                'isFollowing' => $isFollowing,
                'time' => $time
            ));
    }
}
header('Content-Type: application/json; charset=utf8');
//json_encode()함수는 첫번째 인자로 string이나 array를 넣으면 그 데이터를 json화시켜 string형태로 출력해주는 함수다.
$json = json_encode(array("requestType" => $requestType, "notificationList" => $data), JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
echo $json;

?>
