<?php
include("connectdatabase.php");//데이터베이스와 연결
$jsonData = json_decode(file_get_contents("php://input"), true);//클라이언트에서 넘어온 json데이터
$requestType = $jsonData['requestType'];
$myAccount = $jsonData['account'];//사용자 account
$searchText = $jsonData['searchText'];//검색어
$lastId = (int)$jsonData['lastId'];//마지막 사용자 아이템의 id
$isSearched = $jsonData['isSearched'];//검색 여부

$list_size = 20;//한 번 로드될 때 보여줄 데이터 수

$start_index = 0;//리스트의 시작 index

$data = array();//사용자 리스트를 담을 배열

//검색어가 존재하는 경우
if ($isSearched === true) {

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


    //user테이블에서 닉네임이나 이름 중 검색어를 포함하는 경우가 있다면 모두 조회를 하는 쿼리문
    $sql = "
        SELECT*FROM user
        WHERE nickname like '%{$searchText}%' OR name like '%{$searchText}%'
        ORDER BY id ASC LIMIT {$start_index}, {$list_size}
    ";
    $result = mysqli_query($conn, $sql);
    $size = mysqli_num_rows($result);

    if ($size > 0) {
        while ($row = mysqli_fetch_array($result)) {

            $account = $row['account'];
            $id = $row['id'];
            $profile = $row['image'];
            $nickname = $row['nickname'];
            $name = $row['name'];

            array_push($data,
                array(
                    'id' => $id,
                    'account' => $account,
                    'profile' => $profile,
                    'nickname' => $nickname,
                    'name' => $name
                ));


        }
    }
    header('Content-Type: application/json; charset=utf8');
    //json_encode()함수는 첫번째 인자로 string이나 array를 넣으면 그 데이터를 json화시켜 string형태로 출력해주는 함수다.
    $json = json_encode(array('requestType' => $requestType, "userList" => $data), JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
    echo $json;
} //검색어가 존재하지 않는 경우
else {


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
            SELECT COUNT(*) FROM follow WHERE id<='{$lastId}' AND following_account='{$myAccount}'
        ";

        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_array($result);
        $start_index = $row[0];
    }
    $sql = "
        SELECT*FROM follow
        WHERE following_account='{$myAccount}'
        ORDER BY id ASC
        LIMIT {$start_index}, {$list_size}
     ";
    $result = mysqli_query($conn, $sql);
    $size = mysqli_num_rows($result);

    if ($size > 0) {
        while ($row = mysqli_fetch_array($result)) {

            $account = $row['followed_account'];
            $id = $row['id'];

            //팔로잉 리스트의 사용자 정보
            $sql = "
                SELECT*FROM user
                WHERE account='{$account}'
            ";

            $result_ = mysqli_query($conn, $sql);
            $row_ = mysqli_fetch_array($result_);

            $profile = $row_['image'];
            $nickname = $row_['nickname'];
            $name = $row_['name'];

            array_push($data,
                array(
                    'id' => $id,
                    'account' => $account,
                    'profile' => $profile,
                    'nickname' => $nickname,
                    'name' => $name
                ));
        }
    }
    header('Content-Type: application/json; charset=utf8');
    //json_encode()함수는 첫번째 인자로 string이나 array를 넣으면 그 데이터를 json화시켜 string형태로 출력해주는 함수다.
    $json = json_encode(array('requestType' => $requestType, "userList" => $data), JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
    echo $json;
}


?>