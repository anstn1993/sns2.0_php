<?php
include("connectdatabase.php");//데이터베이스와 연결

$jsonData = json_decode(file_get_contents("php://input"), true);//json String -> json Object

//사용자 계정
$account = $jsonData['account'];

//전체 채팅방에서 확인하지 않은 메세지를 모두 조회하는 쿼리
$sql = "
    SELECT COUNT(*) AS count FROM chat
    WHERE receiver like '%{$account}%' AND unchecked_participant like '%{$account}%'
";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($result);

$responseBody = array(
    'requestType' => 'messageCount',
    'messageCount' => (int)$row['count']
);
header('Content-Type: application/json: charset = utf-8');
echo json_encode($responseBody, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
?>
