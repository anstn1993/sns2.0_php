<?php
include("connectdatabase.php");//데이터베이스와 연결
$jsonData = json_decode(file_get_contents("php://input"), true);//클라이언트에서 넘어온 json데이터
$requestType = $jsonData['requestType'];
$account = $jsonData['account'];//현재 로그인한 사용자 계정
$roomNum = (int)$jsonData['roomNum'];//채팅방 번호
$list_size = (int)$jsonData['listSize'];//페이징시 한 페이지에서 보여줄 메세지 개수
$start_index = (int)$jsonData['lastId'];//리스트의 시작 index
$isFirstLoad = $jsonData['isFirstLoad'];//최초 호출 여부(페이징을 위해서 해당 페이지를 다시 조회할 때는 false가 된다.)

if ($start_index != 0) {
    $sql = "
        SELECT COUNT(*) FROM chat WHERE id>='{$start_index}' AND roomNum='{$roomNum}'
    ";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($result);
    $start_index = $row[0];
}

$sql = "
    select*from chat, (select account, nickname, image from user)user
    where chat.sender = user.account and roomNum='{$roomNum}'
    order by id desc limit {$start_index}, {$list_size}
";
$result = mysqli_query($conn, $sql);
$size = mysqli_num_rows($result);
$data = array();//채팅 데이터를 담을 어레이
//채팅 데이터가 존재하면
if ($size > 0) {
    //넘어온 채팅방에 해당하는 행을 돌면서 필드 값들을 $data에 담아준다.
    while ($row = mysqli_fetch_array($result)) {
        $id = $row['id'];//채팅 id
        $sender = $row['sender'];//송신자 계정
        $profile = $row['image'];//프로필 사진
        //프로필 사진을 설정하지 않은 경우
        if (empty($profile) || $profile == '') {
            $profile = "null";
        }
        $nickname = $row['nickname'];//닉네임
        $message = $row['message'];//채팅 메세지

        $imageList = array($row['image1'], $row['image2'], $row['image3'], $row['image4'], $row['image5'], $row['image6']);
        foreach ($imageList as $key => $image) {
            if (empty($imageList[$key]) || $imageList[$key] == "") {
                unset($imageList[$key]);
            }
        }//채팅 이미지 배열
        $type = $row['type'];
        $time = $row['time'];
        $unCheckedParticipant = $row['unchecked_participant'];
        //송신자가 자기 자신이 아닌 메세지의 미확인자 리스트에 자기 자신의 계정이 포함되어있으면 자기 계정을 지워준다.
        if ($sender != $account) {
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
                if (count($unCheckedParticipantList) == 1) {
                    $sql = "
                        UPDATE chat SET 
                        unchecked_participant = REPLACE(unchecked_participant, '{$account}', '')
                        WHERE id = '{$id}'
                    ";
                    $result_ = mysqli_query($conn, $sql);
                } //미확인자가 복수인 경우
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

            //업데이트한 확인자 목록을 다시 변수에 담아준다.
            $sql = "
                    SELECT unchecked_participant FROM chat
                    WHERE id = '{$id}'
                ";
            $result_ = mysqli_query($conn, $sql);
            $row_ = mysqli_fetch_array($result_);
            $unCheckedParticipant = $row_['unchecked_participant'];
        }

        if (empty($unCheckedParticipant) || $unCheckedParticipant == "") {//미확인자 리스트에 아무 값이 없는 경우
            $unCheckedParticipant = "null";//null을 넣어준다.
        }

        $isMyContent = false;
        //현재 로그인한 사용자($account)와 메세지를 보낸 사람($sender)의 계정이 같으면 내가 보낸 메세지
        if ($sender == $account) {
            $isMyContent = true;
        }

        $isExit = false;//사용자가 나갔다는 메세지인 경우
        if ($type == 'exit') {
            $isExit = true;
        }

        $isAddedParticipantMessage = false;//사용자가 추가되었다는 메세지인 경우
        if ($type == 'added') {
            $isAddedParticipantMessage = true;
        }

        array_push($data, array(
            'id' => $id,
            'roomNum' => $roomNum,
            'profile' => $profile,
            'account' => $sender,
            'nickname' => $nickname,
            'message' => $message,
            'imageList' => $imageList,
            'type' => $type,
            'time' => $time,
            'unCheckedParticipant' => $unCheckedParticipant,
            'isMyContent' => $isMyContent,
            'isSent' => true,
            'isImageFromServer' => true,
            'isTimeDivider' => false,
            'isExit' => $isExit,
            'isAddedParticipantMessage' => $isAddedParticipantMessage
        ));
    }
}

//채팅방에 들어와서 최초로 호출되는 경우
if ($isFirstLoad === true) {
    //채팅방에 있는 전체 사진 데이터를 담아주기 위해서 sql을 선언한다.
    $sql = "
        SELECT account, nickname, image, time, image1, image2, image3, image4, image5, image6 FROM chat
        JOIN user ON chat.sender=user.account
        WHERE image1 <> '' AND roomNum = '{$roomNum}'
        ";

    $result = mysqli_query($conn, $sql);
    $size = mysqli_num_rows($result);
    if ($size > 0) {
        //전체 사진 데이터를 json형태로 담기 위해서 배열 선언
        $totalImageData = array();
        while ($row = mysqli_fetch_array($result)) {
            $account = $row['account'];
            $nickname = $row['nickname'];
            $profile = $row['image'];
            $time = $row['time'];
            $imageList = array($row['image1'], $row['image2'], $row['image3'], $row['image4'], $row['image5'], $row['image6']);//이미지 배열
            foreach ($imageList as $key => $value) {//반복문을 통해서 값이 없는 이미지의 경우 배열에서 제거해준다.
                if (empty($imageList[$key]) || $imageList[$key] == '') {
                    unset($imageList[$key]);
                }
            }
            for ($i = 0; $i < count($imageList); $i++) {//이미지 배열의 크기만큼 반복문을 돌면서 데이터를 추가해준다.
                array_push($totalImageData, array(
                    'account' => $account,
                    'nickname' => $nickname,
                    'profile' => $profile,
                    'time' => $time,
                    'image' => $imageList[$i]
                ));
            }
        }
        header('Content-Type: application/json; charset=utf8');
        //json_encode()함수는 첫번째 인자로 string이나 array를 넣으면 그 데이터를 json화시켜 string형태로 출력해주는 함수다.
        $json = json_encode(array("requestType" => $requestType, "chatContentList" => $data, "totalImageData" => $totalImageData), JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
        echo $json;
    } else {
        header('Content-Type: application/json; charset=utf8');
        //json_encode()함수는 첫번째 인자로 string이나 array를 넣으면 그 데이터를 json화시켜 string형태로 출력해주는 함수다.
        $json = json_encode(array("requestType" => $requestType, "chatContentList" => $data), JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
        echo $json;
    }
} //최초 호출이 아닌 경우
else {
    header('Content-Type: application/json; charset=utf8');
    //json_encode()함수는 첫번째 인자로 string이나 array를 넣으면 그 데이터를 json화시켜 string형태로 출력해주는 함수다.
    $json = json_encode(array("requestType" => $requestType, "chatContentList" => $data), JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
    echo $json;
}


?>