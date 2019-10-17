<?php
include("connectdatabase.php");//데이터베이스와 연결
$jsonData = json_decode(file_get_contents("php://input"), true);
$requestType = $jsonData['requestType'];
$searchText = $jsonData['searchText'];//검색어
//검색어에 #이 포함된 경우
if (strpos($searchText, '#') !== false) {
    //#을 기준으로 문자열을 분리한 후
    $firstSplit = explode('#', $searchText);
    //#뒤의 문자열만 다시 넣어준다.
    $searchText = $firstSplit[1];
}
$isSearched = $jsonData['isSearched'];//검색 여부
$list_size = (int)$jsonData['listSize'];//한 번 로드될 때 보여줄 데이터 수
$start_index = $jsonData['lastId'];//현재 로드된 게시물의 마지막 id
$data = array();//팔로잉 리스트를 담을 배열

//검색어가 존재하는 경우
if ($isSearched == true) {
    //user테이블에서 닉네임이나 이름 중 검색어를 포함하는 경우가 있다면 모두 조회를 하는 쿼리문
    $sql = "
  SELECT*FROM hashtag
  WHERE tag like '%{$searchText}%'
  ORDER BY tag ASC
  LIMIT {$start_index}, {$list_size}
  ";
    $result = mysqli_query($conn, $sql);
    $size = mysqli_num_rows($result);

    if ($size > 0) {
        while ($row = mysqli_fetch_array($result)) {

            $id = $row['id'];
            //해당 태그를 가진 최신 게시물의 정보
            $tag = $row['tag'];
            $tag_ = '#' . $row['tag'];
            $count = $row['count'];
            $sql = "
      SELECT*FROM post
      WHERE article like '%{$tag_}%'
      ORDER BY id DESC
      ";

            $result_ = mysqli_query($conn, $sql);
            $row_ = mysqli_fetch_array($result_);

            $image = $row_['image1'];

            array_push($data,
                array(
                    'id' => $id,
                    'tag' => $tag,
                    'image' => $image,
                    'count' => $count
                ));
        }
    }
    header('Content-Type: application/json; charset=utf8');
    //json_encode()함수는 첫번째 인자로 string이나 array를 넣으면 그 데이터를 json화시켜 string형태로 출력해주는 함수다.
    $json = json_encode(array("requestType" => $requestType, "tagList" => $data), JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
    echo $json;
} //검색어가 존재하지 않는 경우
else {
    $sql = "
  SELECT*FROM hashtag
  ORDER BY tag ASC
  LIMIT {$start_index}, {$list_size}
  ";
    $result = mysqli_query($conn, $sql);
    $size = mysqli_num_rows($result);

    if ($size > 0) {
        while ($row = mysqli_fetch_array($result)) {

            $id = $row['id'];
            //해당 태그를 가진 최신 게시물의 정보
            $tag = $row['tag'];
            $tag_ = '#' . $row['tag'];
            $count = $row['count'];
            $sql = "
      SELECT*FROM post
      WHERE article like '%{$tag_}%'
      ORDER BY id DESC
      ";

            $result_ = mysqli_query($conn, $sql);
            $row_ = mysqli_fetch_array($result_);
            $type = "image";
            $type = "image";//게시물 타입
            //게시물이 비디오 타입인 경우
            if (!empty($row_['video']) || $row_['video'] != "") {
                $type = "video";
            }
            $image = $row_['image1'];
            $video = $row_['video'];
            array_push($data,
                array(
                    'id' => $id,
                    'type'=>$type,
                    'tag' => $tag,
                    'video'=>$video,
                    'image' => $image,
                    'count' => $count
                ));
        }
    }
    header('Content-Type: application/json; charset=utf8');
    //json_encode()함수는 첫번째 인자로 string이나 array를 넣으면 그 데이터를 json화시켜 string형태로 출력해주는 함수다.
    $json = json_encode(array("requestType" => $requestType, "tagList" => $data), JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
    echo $json;
}

?>
