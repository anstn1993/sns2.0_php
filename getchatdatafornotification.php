<?php
//이 api에서는 채팅 알림이 왔을 때 intent데이터로 참여자 리스트와 미확인 메세지 수를 추가적으로 넣어줘야하기 때문에 그 데이터를 response해준다.
include("connectdatabase.php");//데이터베이스와 연결
$jsonData = json_decode(file_get_contents("php://input"), true);//클라이언트에서 넘어온 json데이터
$roomNum = $jsonData['roomNum'];//알림을 보내온 채팅방 번호
$account = $jsonData['account'];//사용자 계정(알림을 받은 본인)
$messageData = $jsonData['messageData'];//클라이언트로 그대로 리턴해줄 메세지 데이터(클라이언트에서 이 메세지를 다시 소켓 서버로 전송해주기 위한 데이터)

$participantDataList = array();//자신을 제외한 채팅방 참여자들의 데이터(계정, 닉네임, 프로필)정보를 담을 배열
//참여자 리스트를 추가하기 위해서 채팅방의 참여자 리스트를 가져온다.
$sql = "
    SELECT participant FROM chatroom WHERE id='{$roomNum}'
";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($result);
$participantString = $row['participant'];
$participantList = explode('/', $participantString);
for ($i = 0; $i < count($participantList); $i++) {
    if ($participantList[$i] != $account) {
        $sql = "
            SELECT account, nickname, image FROM user 
            WHERE account = '{$participantList[$i]}'
        ";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_array($result);
        $userAccount = $row['account'];
        $nickname = $row['nickname'];
        $profile = $row['image'];
        array_push($participantDataList, array(
            'account' => $userAccount,
            'nickname' => $nickname,
            'profile' => $profile
        ));
    }
}
//미확인 메세지의 수를 구하는 쿼리문(이때 알림을 클릭해서 들어가는 채팅방의 미확인 메세지는 제외한다.)
$sql = "
    SELECT COUNT(*) AS count FROM chat
    WHERE unchecked_participant LIKE '%{$account}%' AND roomNum <> '{$roomNum}'
";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($result);
$newMessageCount = $row['count'];
$messageData['participantList'] = $participantDataList;
$messageData['newMessageCount'] = $newMessageCount;
$data = array('requestType'=>'getDataForNotification', 'messageData'=>$messageData);//채팅 데이터를 추가할 연관배열 선언
header('Content-Type: application/json; charset=utf8');
echo json_encode($data, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
?>