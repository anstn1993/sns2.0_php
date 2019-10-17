<?php
include("connectdatabase.php");//데이터베이스와 연결

//$_POST, $_GET과 같은 글로벌 메소드는 requestBody로 넘어온 form데이터를 배열로 파싱하여 연관배열처럼 사용할 수 있게 해주는데 json형식으로 보내게 되면 키값을 알 수 없게 되어서
//오류가 발생한다. 그래서 json은 변형을 거치지 않고 전송할 때의 데이터 형태 그대로를 가져와서 사용할 필요가 생기는데 그대 php://input을 사용하여 원시형태 그대로 데이터를 가져올 수 있게 된다.
$jsonData = json_decode(file_get_contents('php://input'), false);//연관 배열을 false로 해줘야만 아래와 같이 $jsonData->account처럼 키값을 참조할 수 있다.
//연관 배열 사용을 true로 해주면 $jsonData['account']와 같은 식으로 키값을 참조해야 한다.
$account = $jsonData->account;

//사용자에게 넘겨받은 아이디로 user테이블 조회
$sql="
  SELECT*FROM user
  WHERE account='{$account}'
";
//쿼리 조회
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($result);
$responseBody = array(
    'requestType'=>"autoLogin",
    'account'=>$row['account'],
    'nickname'=>$row['nickname'],
    'profile'=>$row['image']
);//클라이언트로 response해줄 json스트링을 만들기 위해서 배열 선언
header('Content-Type: application/json; charset=utf8');
$jsonString = json_encode($responseBody, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
echo $jsonString;


 ?>
