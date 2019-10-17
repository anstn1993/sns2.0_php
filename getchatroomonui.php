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


//사용자 계정
$userAccount = $jsonData['account'];


//사용자가 참여중인 채팅방의 데이터를 담을 배열
$data = array();
$sql = "
SELECT*FROM chat, (SELECT MAX(id) AS maxid FROM chat GROUP BY roomNum)latestchat, (SELECT id, participant, activated_participant FROM chatroom)chatroom
WHERE chat.id = latestchat.maxid AND chat.roomNum = chatroom.id AND participant LIKE '%{$userAccount}%' AND activated_participant LIKE '%{$userAccount}%'
ORDER BY time DESC
";

$result = mysqli_query($conn, $sql);
$size = mysqli_num_rows($result);
//참여중인 채팅방이 존재한다면
if ($size > 0) {
    while ($row = mysqli_fetch_array($result)) {
        //채팅방 번호
        $roomNum = $row['id'];
        //채팅방의 가장 마지막 메세지
        $message = $row['message'];
        //메세지 타입
        $type = $row['type'];
        //채팅방의 가장 마지막 메세지 시간
        $time = $row['time'];
        //미확인 메세지의 개수
        $newMessageCount = 0;
        //메세지를 미확인한 사람 리스트
        $unCheckedParticipant = $row['unchecked_participant'];

        //가장 최근 메세지가 자신의 메세지인 경우에는 모든 메세지를 확인한 상태이기 때문에 마지막 메세지가 다른 사람이 보낸 메세지인 경우에만 확인 작업을 거친다.
        //또한 메세지 타입이 exit인 경우에도 확인 작업을 거치지 않는다.
        if ($row['sender'] != $userAccount) {
            //가장 최근 메세지 미확인자 리스트에 자신의 계정이 불포함되어있으면 모든 메세지를 확인한 것이기 때문에
            //최근 메세지 미확인 리스트에 자기 계정이 포함되어있는 경우에만 미확인 메세지를 확인해서 카운트해주는 작업을 해준다.
            if (strpos($unCheckedParticipant, $userAccount) !== false) {
                //채팅 테이블을 id를 기준으로 내림차순 정렬해서 메세지 미확인자 리스트를 조회한다. 여기서 보낸 사람이 자기 자신인 경우에는 카운트에서 제외시켜야 한다.
                $sql = "
                    SELECT unchecked_participant FROM chat
                    WHERE roomNum = '{$roomNum}' AND sender <> '{$userAccount}'
                    ORDER BY id DESC        
                ";
                $result_ = mysqli_query($conn, $sql);
                //메세지 미확인자 리스트에 본인의 계정이 포함되어있을때까지 계속 카운트를 하다가 자신의 계정이 포함지 않는 순간이 나오면 break
                while ($row_ = mysqli_fetch_array($result_)) {
                    //메세지 미확인자 리스트에 자신이 있으면 해당 메세지를 아직 확인하지 않은 것이기 때문에
                    if (strpos($row_['unchecked_participant'], $userAccount) !== false) {
                        //읽지 않은 메세지 카운트 +1
                        $newMessageCount += 1;
                    } //메세지 확인자 리스트에 자신이 포함되어있지 않으면
                    else {
                        //반복문을 탈출한다.
                        break;
                    }
                }
            }
        }
        //채팅방에 참여하고 있는 사람들의 리스트
        $participantList = explode('/', $row['participant']);
        //채팅방에 참여하고 있는 사람들의 데이터를 담을 어레이
        $userList = array();

        //채팅방 잠여자들의 닉네임과 프로필 사진 파일명을 userList배열에 추가해준다.
        for ($i = 0; $i < count($participantList); $i++) {
            //자기 자신은 넣지 않는다.
            if ($participantList[$i] != $userAccount) {
                $sql = "
                    SELECT*FROM user
                    WHERE account='{$participantList[$i]}'
                ";
                $result_ = mysqli_query($conn, $sql);
                $row_ = mysqli_fetch_array($result_);
                $account = $row_['account'];
                $nickname = $row_['nickname'];
                $profile = $row_['image'];
                if (empty($profile) || $profile == '') {
                    $profile = "null";
                }
                //userData를 userList배열에 추가
                array_push($userList, new User($account, $nickname, $profile));//클래스 형태로 정의를 해서 json_encode를 하면 하나의 json객체로 만들어진다.
            }
        }

        //데이터를 배열에 넣어준다.
        array_push($data, array(
            'roomNum' => $roomNum,
            'message' => $message,
            'type' => $type,
            'time' => $time,
            'newMessageCount' => $newMessageCount,
            'userList' => $userList
        ));

    }
}

header('Content-Type: application/json; charset=utf8');
//json_encode()함수는 첫번째 인자로 string이나 array를 넣으면 그 데이터를 json화시켜 string형태로 출력해주는 함수다.
$json = json_encode(array("requestType" => "getChatRoom", "chatroomList" => $data), JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
echo $json;

?>
