<?php
include("connectdatabase.php");//데이터베이스와 연결
$jsonData = json_decode(file_get_contents("php://input"), true);
$requestType = $jsonData['requestType'];//요청 타입

$searchText = $jsonData['searchText'];//검색어
$isSearched = $jsonData['isSearched'];//검색 여부
$list_size =(int)$jsonData['listSize'];//한 번 로드될 때 보여줄 데이터 수
$start_index = (int)$jsonData['lastId'];//리스트의 시작 index

//팔로잉 리스트를 담을 배열
$data = array();

//검색어가 존재하는 경우
if($isSearched === true){
  //post테이블에서 address가 검색어를 포함하는 경우가 있다면 모두 조회를 하는 쿼리문
  $sql="
  SELECT*, COUNT(*) AS total_Count FROM post
  WHERE address like '%{$searchText}%' AND address <>''
  GROUP BY address
  ORDER BY address ASC
  LIMIT {$start_index}, {$list_size}
  ";
  $result = mysqli_query($conn, $sql);
  $size = mysqli_num_rows($result);

  if($size > 0){
    while($row=mysqli_fetch_array($result)){

      $latitude = $row['latitude'];
      $longitude = $row['longitude'];
      $address = $row['address'];
      $id = $row['id'];
      $total_Count = $row['total_Count'];

        array_push($data,
          array(
              'id'=>$id,
              'address'=>$address,
              'latitude'=>$latitude,
              'longitude'=>$longitude,
              'totalCount'=>$total_Count
          ));
    }
  }
  header('Content-Type: application/json; charset=utf8');
  //json_encode()함수는 첫번째 인자로 string이나 array를 넣으면 그 데이터를 json화시켜 string형태로 출력해주는 함수다.
  $json=json_encode(array("requestType"=>$requestType, "placeList"=>$data), JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE);
  echo $json;
}
//검색어가 존재하지 않는 경우
else {
  $sql="
  SELECT *, COUNT(*) AS total_Count FROM post
  WHERE address <>''
  GROUP BY address
  ORDER BY address ASC
  LIMIT {$start_index}, {$list_size}
  ";
  $result = mysqli_query($conn, $sql);
  $size = mysqli_num_rows($result);

  if($size > 0){
    while($row=mysqli_fetch_array($result)){
        $address = $row['address'];
        $latitude = $row['latitude'];
        $longitude = $row['longitude'];
        $id = $row['id'];
        $total_Count = $row['total_Count'];
          array_push($data,
            array(
                'id'=>$id,
                'address'=>$address,
                'latitude'=>$latitude,
                'longitude'=>$longitude,
                'totalCount'=>$total_Count
            ));

    }
  }
  header('Content-Type: application/json; charset=utf8');
  //json_encode()함수는 첫번째 인자로 string이나 array를 넣으면 그 데이터를 json화시켜 string형태로 출력해주는 함수다.
  $json=json_encode(array("requestType"=>$requestType, "placeList"=>$data), JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE);
  echo $json;
}

 ?>
