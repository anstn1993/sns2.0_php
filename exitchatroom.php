<?php
include("connectdatabase.php");//데이터베이스와 연결
//클라이언트에서 넘어온 json데이터
$jsonData = json_decode(file_get_contents("php://input"), true);

$roomNum = $jsonData['roomNum'];//나가는 채팅방 번호
$account = $jsonData['account'];//나가는 사용자의 계정
$nickname = $jsonData['nickname'];//나가는 사용자 닉네임
$profile = $jsonData['profile'];//나가는 사용자 프로필
$message = $jsonData['message'];//chat 테이블에 저장될 message
$type = $jsonData['type'];//메세지 타입
//chat 테이블에 저장될 receiver
$receiver = implode("/", $jsonData['receiverList']);//implode함수는 두번째 인자로 들어온 배열의 값들을 첫번째 인자를 구분자로 둬서 하나의 문자열로 만들어주는 함수
//chat 테이블에 저장될 time
$time = date("Y-m-d H:i:s");
$position = $jsonData['position'];//리사이클러뷰에서 나가는 채팅방 index

//해당 채팅방의 참여자 목록을 조회
$sql = "
    SELECT participant FROM chatroom 
    WHERE id = '{$roomNum}'
";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($result);
//참여자 목록을 담고 있는 String(구분자 '/')
$participantString = $row['participant'];
//참여자 목록을 담는 String을 '/'을 기준으로 쪼개서 배열로 만든다.
$participantList = explode('/', $participantString);
//현재 채팅방 참여자가 두명 이상인 경우
if (count($participantList) >= 2) {
    //채팅방에서 $account만 잘라낸다.
    //자신의 계정이 리스트의 맨 마지막에 위치하는 경우와 그렇지 않은 경우로 나누어서 제거하는 문자열을 다르게 해야 한다.
    $index = 0;
    for ($i = 0; $i < count($participantList); $i++) {
        if ($account == $participantList[$i]) {
            $index = $i;
        }
    }
    //자신의 계정이 맨 마지막에 위치하는 경우
    if ($index == count($participantList) - 1) {
        //계정만 지워준다.
        $deleteString = '/' . $account;
        $sql = "
            UPDATE chatroom SET
            participant = REPLACE(participant, '{$deleteString}','')
            WHERE id = '{$roomNum}'
        ";
        $result = mysqli_query($conn, $sql);
        //activated_participant필드의 값에도 자신의 계정을 지워준다.
        $sql = "
            UPDATE chatroom SET
            activated_participant = REPLACE(activated_participant, '{$deleteString}','')
            WHERE id = '{$roomNum}'
        ";
        $result = mysqli_query($conn, $sql);

    } //자신의 계정이 맨 마지막에 위치하지 않는 경우
    else {
        //계정+'/'를 지워준다.
        $deleteString = $account . '/';
        $sql = "
            UPDATE chatroom SET
            participant = REPLACE(participant, '{$deleteString}','')
            WHERE id = '{$roomNum}'
        ";
        $result = mysqli_query($conn, $sql);
        //activated_participant필드의 값에도 자신의 계정을 지워준다.
        $sql = "
            UPDATE chatroom SET
            activated_participant = REPLACE(activated_participant, '{$deleteString}','')
            WHERE id = '{$roomNum}'
        ";
        $result = mysqli_query($conn, $sql);

    }
    //나간 사용자를 채팅 메세지 미확인자 리스트에서 모두 지워준다.
    $sql = "
        SELECT id, unchecked_participant FROM chat
        WHERE roomNum = '{$roomNum}' AND sender <> '{$account}'
    ";
    $result = mysqli_query($conn, $sql);
    while ($row_ = mysqli_fetch_array($result)) {
        //메세지 미확인자 목록 스트링
        $unCheckedPatricipant = $row_['unchecked_participant'];
        //메세지 id
        $id = $row_['id'];
        //미확인자 스트링에 나간 사용자가 들어있는 경우에만 다음 작업 실행
        if (strpos($unCheckedPatricipant, $account) !== false) {
            //각 채팅 메세지의 확인자 목록
            $unCheckedParticipantList = explode('/', $unCheckedPatricipant);
            //나간 사용자의 계정이 있는 인덱스
            $index = 0;
            for ($i = 0; $i < count($unCheckedParticipantList); $i++) {
                if ($unCheckedParticipantList[$i] == $account) {
                    $index = $i;
                }
            }
            //미확인자가 자기 자신만 남은 경우
            if (count($unCheckedParticipantList) == 1) {
                $sql = "
                    UPDATE chat SET
                    unchecked_participant = REPLACE(unchecked_participant, '{$account}', '')
                    WHERE id = '{$id}'
                ";
                $result_ = mysqli_query($conn, $sql);
            } //자기 이외에도 미확인자가 존재하는 경우
            else {
                //나간 사용자의 계정이 리스트의 마지막에 있다면
                if ($index == count($unCheckedParticipantList) - 1) {
                    $deleteString = '/' . $account;
                    $sql = "
                    UPDATE chat SET
                    unchecked_participant = REPLACE(unchecked_participant, '{$deleteString}', '')
                    WHERE id = '{$id}'
                ";
                    $result_ = mysqli_query($conn, $sql);
                } //사용자의 계정이 리스트의 처음이나 중간에 있다면
                else {
                    $deleteString = $account . '/';
                    $sql = "
                    UPDATE chat SET
                    unchecked_participant = REPLACE(unchecked_participant, '{$deleteString}', '')
                    WHERE id = '{$id}'
                ";
                    $result_ = mysqli_query($conn, $sql);
                }
            }

        }
    }

    $sql = "
        INSERT INTO chat (roomNum, sender, receiver, message, time, type) 
        VALUES(
          '$roomNum',
          '$account',
          '$receiver',
          \"$message\",
          '$time',
          '$type'
        )
    ";
    $result = mysqli_query($conn, $sql);
    //방금 추가된 채팅 데이터의 id조회
    $sql = "
        SELECT id FROM chat
        ORDER BY id DESC 
        LIMIT 1 
    ";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($result);
    $id = $row['id'];
    $data = array(
        'type' => $type,
        'roomNum' => $roomNum,
        'account' => $account,
        'nickname' => $nickname,
        'profile' => $profile,
        'message' => $message,
        'id' => $id,
        'time' => $time,
    );
    header('Content-Type: application/json; charset=utf8');
    //json string으로 인코딩한 값을 클라이언트에 보내주기 위한 echo 설정
    echo json_encode(array('requestType' => 'exitChatRoom', 'exitType' => 0, 'position' => $position, 'returnData' => $data), JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);

} //채팅방 참여자가 한명 남은 경우
else {
    //해당 채팅방 row를 삭제한다.
    $sql = "
        DELETE FROM chatroom
        WHERE id = '{$roomNum}'
    ";
    $result = mysqli_query($conn, $sql);
    //해당 채팅방에 있던 이미지를 다 지워준다.
    $sql = "
        SELECT*FROM chat
        WHERE roomNum = '{$roomNum}'
    ";
    $result = mysqli_query($conn, $sql);
    while ($row_ = mysqli_fetch_array($result)) {
        $image1 = $row_['image1'];
        $image2 = $row_['image2'];
        $image3 = $row_['image3'];
        $image4 = $row_['image4'];
        $image5 = $row_['image5'];
        $image6 = $row_['image6'];
        //이미지 파일들을 지워준다.
        if (!empty($image1)) {
            unlink('chatimage/' . $image1);
        }

        if (!empty($image2)) {
            unlink('chatimage/' . $image2);
        }

        if (!empty($image3)) {
            unlink('chatimage/' . $image3);
        }

        if (!empty($image4)) {
            unlink('chatimage/' . $image4);
        }

        if (!empty($image5)) {
            unlink('chatimage/' . $image5);
        }

        if (!empty($image6)) {
            unlink('chatimage/' . $image6);
        }
    }
    //해당 채팅방의 chat데이터들도 삭제해준다.
    $sql = "
        DELETE FROM chat
        WHERE roomNum='{$roomNum}'
    ";
    $result = mysqli_query($conn, $sql);

    header('Content-Type: application/json; charset=utf8');
    //json string으로 인코딩한 값을 클라이언트에 보내주기 위한 echo 설정
    echo json_encode(array('requestType' => 'exitChatRoom', 'exitType' => 1, 'position' => $position), JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
}

?>
