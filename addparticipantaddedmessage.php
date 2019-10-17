<?php
include("connectdatabase.php");//데이터베이스와 연결
//사용자 초대 메세지 데이터를 json객체로 변환해준다.
$messageData = json_decode(file_get_contents("php://input"), true);//클라이언트에서 넘어온 json데이터

$roomNum = $messageData['roomNum'];//채팅방 번호
$sender = $messageData['account'];//송신자
$receiverList = $messageData['receiverList'];//수신자 리스트
//수신자 스트링으로 변환
$receiver = implode("/", $receiverList);
$message = $messageData['message'];//메세지
$type = $messageData['type'];//메세지 타입
$addedParticipantList = $messageData['addedParticipantList'];
$time = date("Y-m-d H:i:s");//메세지 전송 시간

$sql = "
    INSERT INTO chat (roomNum, sender, receiver, message, time, type)
    VALUES (
    '$roomNum',
    '$sender',
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
    'roomNum' => $roomNum,
    'account' => $sender,
    'receiverList' => $receiverList,
    'message' => $message,
    'type' => $type,
    'addedParticipantList'=>$addedParticipantList,
    'id' => $id,
    'time' => $time,

);
header('Content-Type: application/json; charset=utf8');
//json string으로 인코딩한 값을 클라이언트에 보내주기 위한 echo 설정
echo json_encode(array('requestType' => 'sendAddedMessage', 'messageData' => $data), JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);

?>
