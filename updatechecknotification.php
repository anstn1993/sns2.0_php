<?php
include("connectdatabase.php");//데이터베이스와 연결
$jsonData = json_decode(file_get_contents("php://input"), true);
//알림 확인 처리를 할 알림 리스트 id
$id = (int)$jsonData['id'];

//알림 확인여부를 1로 바꿔서 true로 전환
$sql = "
UPDATE notification
SET
is_checked=1
WHERE id={$id}
";
$result = mysqli_query($conn, $sql);

//성공적으로 update가 된 경우
if ($result) {
    $data = array("requestType" => "updateCheck", "result" => "success");//통신에 성공하면
    echo json_encode($data, JSON_UNESCAPED_UNICODE+JSON_PRETTY_PRINT);
} //업데이트에 실패한 경우
else {
    $data = array("requestType" => "updateCheck", "result" => "fail");//통신에 성공하면
    echo json_encode($data, JSON_UNESCAPED_UNICODE+JSON_PRETTY_PRINT);
}


?>
