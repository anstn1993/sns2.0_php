<?php
include("connectdatabase.php");//데이터베이스와 연결
$jsonData = json_decode(file_get_contents("php://input"), true);//클라이언트에서 넘어온 json데이터
//채팅방 번호
$roomNum = $jsonData['roomNum'];
//초대된 사용자의 데이터를 담는 jsonarray 스트링 ex) "{"participantList": [{"account":"anstn1993","nickname":"만수","profile":"..."},{...},{...}]}"
$addedParticipantData = $jsonData['participantList'];
//참여자의 계정만 담을 배열(여기에 추가된 계정만 넣어서 implode로 문자열화 할 것)
$addedAccountList = array();
//사용자의 계정만 뽑아내서 participant필드에 추가해줄 스트링을 만들어준다.
for ($i = 0; $i < count($addedParticipantData); $i++) {
    $account = $addedParticipantData[$i]['account'];
    array_push($addedAccountList, $account);
}
$addedParticipantString = '';
//한명 추가한 경우
if(count($addedParticipantData) == 1) {
    $addedParticipantString = '/'.implode($addedAccountList);
}
//두명 이상인 경우
else {
    $addedParticipantString = '/'.implode("/", $addedAccountList);
}
//chatroom 테이블의 participant필드의 값을 채팅방에 추가된 사용자를 포함한 participant스트링으로 update해준다.
$sql = "
    UPDATE chatroom SET participant = CONCAT(participant, '{$addedParticipantString}')
    WHERE id = '{$roomNum}'
";
$result = mysqli_query($conn, $sql);
//테이블에 잘 업데이트가 됐으면
if ($result) {
    $json = json_encode(array('requestType'=>'addParticipant', 'participantList'=>$addedParticipantData), JSON_UNESCAPED_UNICODE+JSON_PRETTY_PRINT);
    echo $json;
}
?>