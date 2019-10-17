<?php
include("connectdatabase.php");//데이터베이스와 연결
$jsonData = json_decode(file_get_contents("php://input"), true);//클라이언트에서 넘어온 json데이터
//채팅방 번호
$roomNum = $jsonData['roomNum'];
//채팅방을 지워준다.
$sql = "
    DELETE FROM chatroom
    WHERE id='{$roomNum}'
";
$result = mysqli_query($conn, $sql);
?>
