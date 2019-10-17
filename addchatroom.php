<?php
class User {//채팅방의 사용자 정보를 정의할 클래스
    function __construct($account, $nickname, $profile) {//생성자
        $this->account = $account;
        $this->nickname = $nickname;
        $this->profile = $profile;
    }
}
include("connectdatabase.php");//데이터베이스와 연결
$jsonData = json_decode(file_get_contents("php://input"), true);

$participantString = $jsonData['participantString'];
$account = $jsonData['account'];

$sql = "
    INSERT INTO chatroom (participant, activated_participant)
    VALUES (
    '$participantString',
    '$account'
    )
";
$result = mysqli_query($conn, $sql);

$sql = "
    SELECT*FROM chatroom 
    ORDER BY id DESC
";

$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($result);

//방금 생성된 채팅방 번호
$roomNum = $row['id'];


//생성된 채팅방에 참여하고 있는 사람들의 정보를 담을 array
$userlist = array();

$participantString = $row['participant'];
$participantList = explode('/', $participantString);

for ($i = 0; $i < count($participantList); $i++) {
    //자기 자신은 넣지 않는다.
    if ($account != $participantList[$i]) {
        $sql = "
        SELECT*FROM user
        WHERE account='{$participantList[$i]}'
    ";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_array($result);
        //사용자의 프로필
        $profile = $row['image'];
        if (empty($row['image'])) {
            $profile = 'null';
        }

        //사용자 계정
        $account = $row['account'];
        //사용자 닉네임
        $nickname = $row['nickname'];
        array_push($userlist, new User($account, $nickname, $profile));

    }

}

//채팅방 번호와 사용자 리스트를 배열에 넣은 후 jason스트링으로 만들어서 클라이언트로 날려준다.
$data = array(
    'roomNum'=>$roomNum,
    'userList'=>$userlist
);
header('Content-Type: application/json; charset=utf8');
//json_encode()함수는 첫번째 인자로 string이나 array를 넣으면 그 데이터를 json화시켜 string형태로 출력해주는 함수다.
$json = json_encode(array("requestType" => "addChatRoom", "addedChatRoom" => $data), JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
echo $json;

?>