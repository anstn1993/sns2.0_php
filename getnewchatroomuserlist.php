<?php

class User {//채팅방 참여자 데이터 클래스
    function __construct($account, $nickname, $profile){//생성자
        $this->account = $account;
        $this->nickname = $nickname;
        $this->profile = $profile;
    }
}
include("connectdatabase.php");//데이터베이스와 연결
$jsonData = json_decode(file_get_contents("php://input"), true);
$myAccount = $jsonData['account'];//사용자 계정
$roomNum = $jsonData['roomNum'];//새롭게 생성된 채팅방 번호
$message = $jsonData['message'];
$time = $jsonData['time'];
//새롭게 생성된 채팅방의 참여자 리스트를 가져오는 쿼리
$sql = "
    SELECT participant FROM chatroom 
    WHERE id = '{$roomNum}'
";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($result);
//채팅방 참여자 리스트 스트링
$participantListString = $row['participant'];
//채팅방 참여자   리스트
$participantList = explode('/', $participantListString);
//클라이언트에 jsonstring으로 전달하기 위한 array
$userList = array();
//자신을 제외한 사용자들의 닉네임과 프로필 파일명을 조회해서 array에 담아준다.
for ($i = 0; $i < count($participantList); $i++) {
    //자기 자신은 넣지 않는다.
    if ($myAccount != $participantList[$i]) {
        $sql = "
            SELECT*FROM user
            WHERE account='{$participantList[$i]}'
        ";
        $result_ = mysqli_query($conn, $sql);
        $row_ = mysqli_fetch_array($result_);
        $account = $row_['account'];
        $nickname = $row_['nickname'];
        $profile = $row_['image'];
        //userData를 userList배열에 추가
        array_push($userList, new User($account, $nickname, $profile));
    }
}

$data = array(
    'roomNum'=>$roomNum,
    'message'=>$message,
    'time'=>$time,
    'newMessageCount'=>1,
    'userList'=>$userList
);
header('Content-Type: application/json: charset=utf8');
//json_encode()함수는 첫번째 인자로 string이나 array를 넣으면 그 데이터를 json화시켜 string형태로 출력해주는 함수다.
$json = json_encode(array('requestType'=>'createNewChatRoom', 'chatRoomData'=>$data), JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE);
echo $json;

?>
