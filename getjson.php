<?php

$host = 'localhost';
$username = 'moonsoo'; # MySQL 계정 아이디
$password = 'Rla933466r!'; # MySQL 계정 패스워드
$dbname = 'SNS';  # DATABASE 이름

$android = strpos($_SERVER['HTTP_USER_AGENT'], "Android");

try {
    $con = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8",$username, $password);
} catch(PDOException $e) {

    die("Failed to connect to the database: " . $e->getMessage());
}
//$con이라는 PDO클래스의 메소드 prepare()를 통해서 쿼리문 준비
$stmt=$con->prepare('select*from person');
//쿼리문 실행
$stmt->execute();

if($stmt->rowcount() > 0) {
  $data=array();

  while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
    //$row[]배열의 키값을 변수화시킨다. 지금 $row['id'], $row['name'], $row['country']가 있는데 이 메소드가 실행되면
    //$id, $name, $country라는 변수가 선언되고 이 변수 안에 배열 값들이 자동 입력됨. 킹왕짱 함수네
    extract($row);

    array_push($data,
      array('id'=>$id,
            'name'=>$name,
            'country'=>$country
      ));
  }

  header('Content-Type: application/json; charset=utf8');
  //json_encode()함수는 첫번째 인자로 string이나 array를 넣으면 그 데이터를 json화시켜 string형태로 출력해주는 함수다. 
  $json=json_encode(array("webnautes"=>$data), JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE);
  echo $json;

}

 ?>
