<?php
include("connectdatabase.php");//데이터베이스와 연결
$jsonData = json_decode(file_get_contents("php://input"), true);
$requestType = $jsonData['requestType'];//요청 타입
$myAccount = $jsonData['account'];//사용자 페이지의 주인 account
$searchText = $jsonData['searchText'];//검색어
$isSearched = $jsonData['isSearched'];//검색 여부
$lastId = (int)$jsonData['lastId'];//현재 로드된 가장 마지막 리스트 id
$list_size = (int)$jsonData['listSize'];//한 번 로드될 때 보여줄 데이터 수

$isFollowing = false;

//리스트의 시작 index
$start_index = 0;

//팔로잉 리스트를 처음 로드할 때는 index를 0으로 해서 처음부터 다 보여준다.
if ($lastId == 0) {
    $start_index = 0;
}
//하지만 이후 페이징이 사직되면 로드되어있는 리스트 중 마지막 index를 보내주기 때문에 마지막 index를 통해
//해당 index의 데이터가 테이블 상에서 몇번째에 해당하는지 알아야 한다.
else {
    //클라이언트에서 넘어온 현재 로드된 가장 마지막 팔로잉데이터의 id보다 작거나 같은 데이터의 수를 구해서
    //그 수를 다음 로드할 데이터의 시작 index로 설정
    $sql = "
    SELECT COUNT(*) FROM user WHERE id<='{$lastId}'
  ";

    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($result);
    $start_index = $row[0];
}

//팔로잉 리스트를 담을 배열
$data = array();

//검색어가 존재하는 경우
if ($isSearched === true) {
    //user테이블에서 닉네임이나 이름 중 검색어를 포함하는 경우가 있다면 모두 조회를 하는 쿼리문
    $sql = "
  SELECT*FROM user
  WHERE nickname like '%{$searchText}%' OR name like '%{$searchText}%'
  LIMIT {$start_index}, {$list_size}
  ";
    $result = mysqli_query($conn, $sql);
    $size = mysqli_num_rows($result);

    if ($size > 0) {
        while ($row = mysqli_fetch_array($result)) {

            $account = $row['account'];
            $id = $row['id'];
            //해당 계정의 사용자 정보
            $sql = "
      SELECT*FROM user
      WHERE account='{$account}'
      ";

            $result_ = mysqli_query($conn, $sql);
            $row_ = mysqli_fetch_array($result_);

            $profile = $row_['image'];
            $nickname = $row_['nickname'];
            $name = $row_['name'];


            //리스트의 사용자 팔로잉 여부
            $sql = "
          SELECT*FROM follow
          WHERE followed_account='{$myAccount}' AND following_account='{$account}'
        ";
            $result_ = mysqli_query($conn, $sql);
            $size = mysqli_num_rows($result_);

            if ($size == 0) {
                $isFollowing = false;
            } else {
                $isFollowing = true;
            }

            array_push($data,
                array(
                    'id' => $id,
                    'account' => $account,
                    'profile' => $profile,
                    'nickname' => $nickname,
                    'name' => $name,
                    'isFollowing' => $isFollowing
                ));
        }
    }
    header('Content-Type: application/json; charset=utf8');
    //json_encode()함수는 첫번째 인자로 string이나 array를 넣으면 그 데이터를 json화시켜 string형태로 출력해주는 함수다.
    $json = json_encode(array("requestType" => $requestType, "userlist" => $data), JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
    echo $json;
} //검색어가 존재하지 않는 경우
else {
    $sql = "
  SELECT*FROM user
  LIMIT {$start_index}, {$list_size}
  ";
    $result = mysqli_query($conn, $sql);
    $size = mysqli_num_rows($result);

    if ($size > 0) {
        while ($row = mysqli_fetch_array($result)) {

            $account = $row['account'];
            $id = $row['id'];

            //해당 계정의 사용자 정보
            $sql = "
      SELECT*FROM user
      WHERE account='{$account}'
      ";

            $result_ = mysqli_query($conn, $sql);
            $row_ = mysqli_fetch_array($result_);

            $profile = $row_['image'];
            $nickname = $row_['nickname'];
            $name = $row_['name'];

            //좋아요 리스트의 사용자 팔로잉 여부
            $sql = "
        SELECT*FROM follow
        WHERE followed_account='{$myAccount}' AND following_account='{$account}'
      ";
            $result_ = mysqli_query($conn, $sql);
            $size = mysqli_num_rows($result_);

            if ($size == 0) {
                $isFollowing = false;
            } else {
                $isFollowing = true;
            }

            array_push($data,
                array(
                    'id' => $id,
                    'account' => $account,
                    'profile' => $profile,
                    'nickname' => $nickname,
                    'name' => $name,
                    'isFollowing' => $isFollowing
                ));
        }
    }
    header('Content-Type: application/json; charset=utf8');
    //json_encode()함수는 첫번째 인자로 string이나 array를 넣으면 그 데이터를 json화시켜 string형태로 출력해주는 함수다.
    $json = json_encode(array("requestType" => $requestType, "userlist" => $data), JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
    echo $json;
}

?>
