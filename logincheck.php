<?php

include("connectdatabase.php");//데이터베이스와 연결

//로그인할 때 입력한 아이디, 비밀번호 값
$jsonData = json_decode(file_get_contents("php://input"), true);
$account = $jsonData['account'];
$password = hash("sha256", $jsonData['password']);
//단말기의 fcm token값
$token = $jsonData['token'];


//사용자에게 넘겨받은 아이디로 user테이블 조회
$sql = "
  SELECT*FROM user
  WHERE account='{$account}'
";
//쿼리 조회
$result = mysqli_query($conn, $sql);
$responseBody = array(
    'requestType' => "login"
);
if (empty($account) || empty($password)) {//이이디나 비밀번호를 적어도 하나 이상 입력하지 않은 경우
    $responseBody['loginResult'] = 'fillCompletely';
    header("Content-Type: application/json: charset = utf8");
    echo json_encode($responseBody, JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE);
} //조회가 안 된 경우
else if ($result === false) {
    $responseBody['loginResult'] = 'checkAgain';
    header("Content-Type: application/json: charset = utf8");
    echo json_encode($responseBody, JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE);
} //조회가 된 경우
else {
    //입력받은 아이디 행을 참조
    $row = mysqli_fetch_array($result);
    //입력받은 비밀번호가 테이블의 비밀번호와 다른 경우
    if ($row['password'] != $password) {
        $responseBody['loginResult'] = 'checkAgain';
        header("Content-Type: application/json: charset = utf8");
        echo json_encode($responseBody, JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE);
    } //입력받은 비밀번호가 테이블의 비밀번호와 일치하는 경우
    else {
        $responseBody['account'] = $row['account'];
        $responseBody['nickname'] = $row['nickname'];
        $responseBody['profile'] = $row['image'];
        $responseBody['loginResult'] = 'success';
        header("Content-Type: application/json: charset = utf8");
        echo json_encode($responseBody, JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE);

        //사용자 정보에 단말기의 고유 token을 저장하고 로그인 상태를 1로 전환한다.
        $sql = "
        UPDATE user SET
        token='{$token}',
        login=1
        WHERE account='{$account}'
      ";
        $result = mysqli_query($conn, $sql);
    }
}


?>
