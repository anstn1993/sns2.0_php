<?php
include("connectdatabase.php");//데이터베이스와 연결
$jsonData = json_decode(file_get_contents("php://input"), true);//클라이언트에서 넘어온 json데이터

//사용자 계정
$account = $jsonData['account'];
//사용자가 참여중인 채팅방 목록만 가져오는 쿼리문
$sql = "
SELECT*FROM chatroom
WHERE participant like '%{$account}%' AND activated_participant like '%{$account}%' 
";

$result = mysqli_query($conn, $sql);
$size = mysqli_num_rows($result);
//참여중인 채팅방이 존재한다면
if ($size > 0) {
    $sql = "
      SELECT*FROM user
      WHERE account = '{$account}'
    ";

    $result_ = mysqli_query($conn, $sql);
    $row_ = mysqli_fetch_array($result_);
    $nickname = $row_['nickname'];//사용자 닉네임
    $profile = $row_['image'];//사용자 프로필
    //사용자가 참여중인 채팅방의 데이터를 담을 배열
    $data = array(
        "type" => "join",
        "account" => $account,
        "nickname" => $nickname,
        "profile" => $profile
    );
    //채팅방 번호를 담을 array
    $roomNumList = array();
    while ($row = mysqli_fetch_array($result)) {
        //채팅방 번호
        $roomNum = (int) $row['id'];
        //데이터를 배열에 넣어준다.
        array_push($roomNumList, $roomNum);
    }
    //채팅방 번호를 담을 배열을 채팅방 데이터에 넣어준다.
    $data['roomNumList'] = $roomNumList;
}
header('Content-Type: application/json; charset=utf8');
//json_encode()함수는 첫번째 인자로 string이나 array를 넣으면 그 데이터를 json화시켜 string형태로 출력해주는 함수다.
$json = json_encode(array('requestType'=>'getChatRoomList','roomData'=>$data), JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
echo $json;




?>
