<?php
include("connectdatabase.php");//데이터베이스와 연결
$jsonData = json_decode(file_get_contents("php://input"), true);
$roomNum = $jsonData['roomNum'];
$account = $jsonData['account'];

$sql = "
    SELECT id, unchecked_participant FROM chat
    WHERE roomNum='{$roomNum}' AND sender<>'{$account}'
";
$result = mysqli_query($conn, $sql);
$result_;
//채팅방의 채팅 데이터를 모두 조회하면서 다음의 로직 실행
while ($row = mysqli_fetch_array($result)) {
    $id = $row['id'];
    //미확인자 리스트가 나열된 스트링(구분자:'/')
    $unCheckedParticipant = $row['unchecked_participant'];
    //메세지 미확인자에 자기 자신이 있는 경우에만 자기 계정을 지워주면 된다.
    if (strpos($unCheckedParticipant, $account) !== false) {
        //자신의 계정이 몇번째에 위치하는지에 따라 지워줘야하는 문자열읻 달라지기 때문에 위치를 파악한다.
        $unCheckedParticipantList = explode('/', $unCheckedParticipant);
        //리스트 속에 자신의 계정 index
        $index = 0;
        for ($i = 0; $i < count($unCheckedParticipantList); $i++) {
            if ($unCheckedParticipantList[$i] == $account) {
                $index = $i;
            }
        }
        //미확인자에 자기 자신만 남아있는 경우
        if(count($unCheckedParticipantList)==1) {
            $sql = "
                        UPDATE chat SET 
                        unchecked_participant = REPLACE(unchecked_participant, '{$account}', '')
                        WHERE id = '{$id}'
                    ";
            $result_ = mysqli_query($conn, $sql);
        }
        //미확인자가 복수인 경우
        else {
            //자신의 계정이 리스트의 마지막에 위치하는 경우
            if ($index == count($unCheckedParticipantList) - 1) {
                $deleteString = '/' . $account;
                $sql = "
                        UPDATE chat SET 
                        unchecked_participant = REPLACE(unchecked_participant, '{$deleteString}', '')
                        WHERE id = '{$id}'
                    ";
                $result_ = mysqli_query($conn, $sql);
            } //자신의 계정이 리스트의 처음이나 중간에 위치하는 경우
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
?>
