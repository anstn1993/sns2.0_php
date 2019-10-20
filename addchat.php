<?php
//클라이언트에서 넘어온 게시물 데이터를 변수에 담아준다.
//이 중에서 article, image(2,3,4,5,6), address, latitude, longitude는 null일 수도 있는데 그래도 데이터베이스에 저장되는 것에는 무관하기 때문에 그냥 둔다.
$roomNum = $_POST['roomNum'];
$account = $_POST['sender'];
$receiver = $_POST['receiver'];
$message = $_POST['message'];
$type = $_POST['type'];
$image1 = $_FILES['image1']['name'];
$image2 = $_FILES['image2']['name'];
$image3 = $_FILES['image3']['name'];
$image4 = $_FILES['image4']['name'];
$image5 = $_FILES['image5']['name'];
$image6 = $_FILES['image6']['name'];
$video = $_FILES['video']['name'];
$time = date("Y-m-d H:i:s");

$host = 'localhost';
$username = 'moonsoo'; # MySQL 계정 아이디
$userpassword = 'Rla933466r!'; # MySQL 계정 패스워드
$dbname = 'SNS';  # DATABASE 이름

//데이터베이스와 연결
$conn = mysqli_connect($host, $username, $userpassword, $dbname);

//chat table의 unchecked_participant필드 값을 채워주기 위해서 chatroom table의 participant값을 가져온다.
$sql = "
    SELECT participant FROM chatroom
    WHERE id = '{$roomNum}'
";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($result);
$participant = $row['participant'];
//참여자 목록에서 자신의 계정이 어디에 위치하느냐에 따라서 제거해줘야 하는 문자열이 달라지기 때문에 목록 속 자신의 계정 index를 찾아준다.
$index = 0;
$participantList = explode('/', $participant);
$unCheckedParticipant= "";
//참여자가 자기 하나만 남은 경우
if(count($participantList)==1) {
    $unCheckedParticipant = "null";
}
else {
    for($i = 0; $i < count($participantList); $i++) {
        if($participantList[$i] == $account) {
            $index = $i;
        }
    }
//chat 테이블의 unchecked_participant필드에 넣어줄 미확인자 리스트 문자열


//자신의 계정이 리스트의 마지막에 위치하는 경우
    if($index == count($participantList)-1) {
        $targetString = '/'.$account;
        $unCheckedParticipant = str_replace($targetString,'',$participant);
    }
//자신의 계정이 리스트의 처음이나 중간에 위치하는 경우
    else {
        $targetString = $account.'/';
        $unCheckedParticipant = str_replace($targetString,'',$participant);
    }
}

//채팅 데이터를 넣어주는 쿼리 실행
$sql = "
INSERT INTO chat (roomNum, sender, receiver, message, image1, image2, image3, image4, image5, image6, video, time, unchecked_participant, type)
VALUES (
'$roomNum',
'$account',
'$receiver',
\"$message\",
'$image1',
'$image2',
'$image3',
'$image4',
'$image5',
'$image6',
'$video',
'$time',
'$unCheckedParticipant',
'$type'
)
";

$result = mysqli_query($conn, $sql);

//회전된 이미지를 처리하기 위해서 exif데이터를 가져와서 이미지를 적절한 방향으로 돌려준다.
$tmp_file1 = $_FILES['image1']['tmp_name'];
$tmp_file2 = $_FILES['image2']['tmp_name'];
$tmp_file3 = $_FILES['image3']['tmp_name'];
$tmp_file4 = $_FILES['image4']['tmp_name'];
$tmp_file5 = $_FILES['image5']['tmp_name'];
$tmp_file6 = $_FILES['image6']['tmp_name'];

//exif_read_data함수는 파일의 meta데이터 정보를 읽어올 수 있도록 해준다.
$exifData1 = exif_read_data($tmp_file1);
$exifData2 = exif_read_data($tmp_file2);
$exifData3 = exif_read_data($tmp_file3);
$exifData4 = exif_read_data($tmp_file4);
$exifData5 = exif_read_data($tmp_file5);
$exifData6 = exif_read_data($tmp_file6);

//jpeg형식의 파일을 만든다.
$src1 = imagecreatefromjpeg($tmp_file1);
$src2 = imagecreatefromjpeg($tmp_file2);
$src3 = imagecreatefromjpeg($tmp_file3);
$src4 = imagecreatefromjpeg($tmp_file4);
$src5 = imagecreatefromjpeg($tmp_file5);
$src6 = imagecreatefromjpeg($tmp_file6);

//이미지 1의 메타 데이터 중 회전값이 존재하는 경우
if (!empty($exifData1['Orientation'])) {
    //오른쪽으로 90도가 회전된 경우
    if ($exifData1['Orientation'] == 8) {
        //이미지를 왼쪽으로 90도 돌려준다.
        $src1 = imagerotate($src1, 90, 0);
    } //오른쪽으로 180도 회전된 경우
    else if ($exifData1['Orientation'] == 3) {
        //이미지를 왼쪽으로 180도 돌려준다.
        $src1 = imagerotate($src1, 180, 0);
    } //왼쪽으로 90도 회전된 경우
    else if ($exifData1['Orientation'] == 6) {
        //이미지를 오른쪽으로 90도 돌려준다.
        $src1 = imagerotate($src1, -90, 0);
    }
}

//이미지 2의 메타 데이터 중 회전값이 존재하는 경우
if (!empty($exifData2['Orientation'])) {
    //오른쪽으로 90도가 회전된 경우
    if ($exifData2['Orientation'] == 8) {
        //이미지를 왼쪽으로 90도 돌려준다.
        $src2 = imagerotate($src2, 90, 0);
    } //오른쪽으로 180도 회전된 경우
    else if ($exifData2['Orientation'] == 3) {
        //이미지를 왼쪽으로 180도 돌려준다.
        $src2 = imagerotate($src2, 180, 0);
    } //왼쪽으로 90도 회전된 경우
    else if ($exifData2['Orientation'] == 6) {
        //이미지를 오른쪽으로 90도 돌려준다.
        $src2 = imagerotate($src2, -90, 0);
    }
}

//이미지 3의 메타 데이터 중 회전값이 존재하는 경우
if (!empty($exifData3['Orientation'])) {
    //오른쪽으로 90도가 회전된 경우
    if ($exifData3['Orientation'] == 8) {
        //이미지를 왼쪽으로 90도 돌려준다.
        $src3 = imagerotate($src3, 90, 0);
    } //오른쪽으로 180도 회전된 경우
    else if ($exifData3['Orientation'] == 3) {
        //이미지를 왼쪽으로 180도 돌려준다.
        $src3 = imagerotate($src3, 180, 0);
    } //왼쪽으로 90도 회전된 경우
    else if ($exifData3['Orientation'] == 6) {
        //이미지를 오른쪽으로 90도 돌려준다.
        $src3 = imagerotate($src3, -90, 0);
    }
}
//이미지 4의 메타 데이터 중 회전값이 존재하는 경우
if (!empty($exifData4['Orientation'])) {
    //오른쪽으로 90도가 회전된 경우
    if ($exifData4['Orientation'] == 8) {
        //이미지를 왼쪽으로 90도 돌려준다.
        $src4 = imagerotate($src4, 90, 0);
    } //오른쪽으로 180도 회전된 경우
    else if ($exifData4['Orientation'] == 3) {
        //이미지를 왼쪽으로 180도 돌려준다.
        $src4 = imagerotate($src4, 180, 0);
    } //왼쪽으로 90도 회전된 경우
    else if ($exifData4['Orientation'] == 6) {
        //이미지를 오른쪽으로 90도 돌려준다.
        $src4 = imagerotate($src4, -90, 0);
    }
}
//이미지 5의 메타 데이터 중 회전값이 존재하는 경우
if (!empty($exifData5['Orientation'])) {
    //오른쪽으로 90도가 회전된 경우
    if ($exifData5['Orientation'] == 8) {
        //이미지를 왼쪽으로 90도 돌려준다.
        $src5 = imagerotate($src5, 90, 0);
    } //오른쪽으로 180도 회전된 경우
    else if ($exifData5['Orientation'] == 3) {
        //이미지를 왼쪽으로 180도 돌려준다.
        $src5 = imagerotate($src5, 180, 0);
    } //왼쪽으로 90도 회전된 경우
    else if ($exifData5['Orientation'] == 6) {
        //이미지를 오른쪽으로 90도 돌려준다.
        $src5 = imagerotate($src5, -90, 0);
    }
}
//이미지 5의 메타 데이터 중 회전값이 존재하는 경우
if (!empty($exifData6['Orientation'])) {
    //오른쪽으로 90도가 회전된 경우
    if ($exifData6['Orientation'] == 8) {
        //이미지를 왼쪽으로 90도를 돌려준다.
        $src6 = imagerotate($src6, 90, 0);
    } //오른쪽으로 180도 회전된 경우
    else if ($exifData6['Orientation'] == 3) {
        //이미지를 왼쪽으로 180도 돌려준다.
        $src6 = imagerotate($src6, 180, 0);
    } //왼쪽으로 90도 회전된 경우
    else if ($exifData6['Orientation'] == 6) {
        //이미지를 오른쪽으로 90도 돌려준다.
        $src6 = imagerotate($src6, -90, 0);
    }
}

//이미지 파일의 경로
$save_dir = './chatimage';
//서버에 회전값이 반영된 이미지 파일들을 업로드한다.
imagejpeg($src1, "$save_dir/$image1");
imagejpeg($src2, "$save_dir/$image2");
imagejpeg($src3, "$save_dir/$image3");
imagejpeg($src4, "$save_dir/$image4");
imagejpeg($src5, "$save_dir/$image5");
imagejpeg($src6, "$save_dir/$image6");

//동영상 파일의 경로
$save_dir = './chatvideo/';
//비디오 파일을 tep_name의 경로(임시 경로)에서 chatvideo경로로 이동해서 저장해준다.
move_uploaded_file($_FILES['video']['tmp_name'], $save_dir.$video);

$sql = "
    SELECT*FROM chat, (SELECT id as roomNum, participant FROM chatroom)chatroom
    WHERE chat.roomNum=chatroom.roomNum 
    ORDER BY chat.id DESC
";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($result);
$id = $row['id'];
$participantString = $row['participant'];
$participantList = explode('/', $participantString);
$participantCount = count($participantList);
//업로드 된 채팅 데이터를 배열에 담는다.
$upload_data = array(
    'id' => $id,
    'roomNum' => $roomNum,
    'sender' => $account,
    'receiver' => $receiver,
    'message' => $message,
    'image1' => $image1,
    'image2' => $image2,
    'image3' => $image3,
    'image4' => $image4,
    'image5' => $image5,
    'image6' => $image6,
    'video' => $video,
    'time' => $time,
    'unCheckedParticipant'=>$unCheckedParticipant
);
//json string으로 인코딩한 값을 클라이언트에 보내주기 위한 echo 설정
echo json_encode($upload_data, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);

?>