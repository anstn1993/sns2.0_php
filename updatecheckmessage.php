<?php
include("connectdatabase.php");//데이터베이스와 연결
$jsonData = json_decode(file_get_contents("php://input"), true);//클라이언트에서 넘어온 json데이터

$id = $jsonData['id'];//메세지 id
$account = $jsonData['account'];//미확인자 리스트에서 지워줄 계정
$checkData = $jsonData['checkData'];//클라이언트로 리턴하여 소켓서버로 전달해줄 데이터
//클라이언트에서 넘어온 채팅 id를 통해서 해당 채팅 row를 조회한다.
$sql = "
    SELECT*FROM chat
    WHERE id = '{$id}'
";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($result);
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
    if(count($unCheckedParticipantList) == 1){
        $sql = "
                        UPDATE chat SET 
                        unchecked_participant = REPLACE(unchecked_participant, '{$account}', '')
                        WHERE id = '{$id}'
                    ";
        $result = mysqli_query($conn, $sql);
        $json = json_encode(array('requestType'=>'checkedMessage', 'checkData'=>$checkData), JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE);
        echo $json;
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
            $result = mysqli_query($conn, $sql);
            $json = json_encode(array('requestType'=>'checkedMessage', 'checkData'=>$checkData), JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE);
            echo $json;
        }
        //자신의 계정이 리스트의 처음이나 중간에 위치하는 경우
        else {
            $deleteString = $account . '/';
            $sql = "
                        UPDATE chat SET 
                        unchecked_participant = REPLACE(unchecked_participant, '{$deleteString}', '')
                        WHERE id = '{$id}'
                    ";
            $result = mysqli_query($conn, $sql);
            $json = json_encode(array('requestType'=>'checkedMessage', 'checkData'=>$checkData), JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE);
            echo $json;
        }
    }
}
?>
