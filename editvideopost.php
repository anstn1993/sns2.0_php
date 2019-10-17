<?php
include("connectdatabase.php");//데이터베이스와 연결
//클라이언트에서 넘어온 게시물 데이터를 변수에 담아준다.
//게시물 id(번호)
$postNum = (int)$_POST['postNum'];
$account = $_POST['account'];
$article = $_POST['article'];
$video = $_FILES['video']['name'];
$address = $_POST['address'];
$latitude = $_POST['latitude'];
$longitude = $_POST['longitude'];


//먼저 기존에 저장되어있던 이미지파일을 다 가져온다.(파일을 삭제해주기 위해서)
$sql = "
  SELECT*FROM post
  WHERE id='{$postNum}'
";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($result);
//클라이언트에서 동영상 파일이 넘어왔으면 기존에 서버에 존재하던 동영상은 지워준다.
if (!empty($_FILES['video'])) {
    unlink('uploadvideo/'.$row['video']);
}


//그리고 기존의 게시글을 가져와서 해시태그를 지워주거나 개수를 -1해준다.
$beforeArticle = $row['article'];
//기존의 해시태그를 담는 배열
$hashTag = array();
//기존 게시글에 해시태그가 있는 경우
if (strpos($beforeArticle, '#') !== false) {
    //해시태그를 기준으로 문자열을 모두 쪼갠다.
    $firstSplit = explode('#', $beforeArticle);

    for ($i = 1; $i < count($firstSplit); $i++) {
        //#해시태그\n(\r, \r\n)문자열 같은 경우에는 개행된 문자까지 태그에 포함되는 문자로 포함하기 때문에 이를 해결하기 위해서
        //개행을 기준으로 문자열을 split해서 배열화하고 그 중 0번째 index만 진짜 태그로 가져간다.
        $non_enter_tag = preg_split('/\r\n|\r|\n/', $firstSplit[$i]);
        //공백을 기준으로 한번 더 쪼개면 쪼개진 문자열 중 가장 첫번째 문자열이 해시태그가 된다.
        $secondSplit = explode(' ', $non_enter_tag[0]);
        //태그를 치고 다음 줄로 넘어가면 개행문자가 추가되는데 이게 같이 있으면 다음 줄로 넘어가지 않고 업로드한 태그와 다른 문자로 인식하기 때문에
        //개행문자를 없애준다.
        $tag = preg_replace('/\r\n|\r|\n/', '', $secondSplit[0]);
        array_push($hashTag, $tag);
    }
}
//기존 게시글에 해시태그가 있다면
if (count($hashTag) != 0) {
    for ($i = 0; $i < count($hashTag); $i++) {
        //해당 해시태그의 태그를 검색해서
        $sql = "
      SELECT*FROM hashtag
      WHERE tag='{$hashTag[$i]}'
    ";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_array($result);
        //태그의 개수가 2이상이면
        if ($row['count'] >= 2) {
            //개수만 -1을 해준다.
            $sql = "
        UPDATE hashtag
        SET
        count = count-1
        WHERE tag ='{$hashTag[$i]}'
      ";
            $result = mysqli_query($conn, $sql);
        } //태그의 개수가 2미만이면
        else {
            //태그 자체를 지워준다.
            $sql = "
        DELETE FROM hashtag
        WHERE tag='{$hashTag[$i]}'
      ";
            $result = mysqli_query($conn, $sql);
        }
    }
}

//새롭게 추가된 해시태그를 추가해준다.
//게시글에 해시태그가 존재한다면
$newHashTag = array();
if (strpos($article, '#') !== false) {
    //해시태그를 기준으로 문자열을 모두 쪼갠다.
    $newFirstSplit = explode('#', $article);
    //해시태그 뒤에 공백 이후 문자열이 존재할 수 있기 때문에 해당 문자열들을 공백 기준으로 다시 쪼개준다.
    for ($i = 1; $i < count($newFirstSplit); $i++) {
        //#해시태그\n(\r, \r\n)문자열 같은 경우에는 개행된 문자까지 태그에 포함되는 문자로 포함하기 때문에 이를 해결하기 위해서
        //개행을 기준으로 문자열을 split해서 배열화하고 그 중 0번째 index만 진짜 태그로 가져간다.
        $non_enter_tag = preg_split('/\r\n|\r|\n/', $newFirstSplit[$i]);
        //공백을 기준으로 한번 더 쪼개면 쪼개진 문자열 중 가장 첫번째 문자열이 해시태그가 된다.
        $newSecondSplit = explode(' ', $non_enter_tag[0]);
        //태그를 치고 다음 줄로 넘어가면 개행문자가 추가되는데 이게 같이 있으면 다음 줄로 넘어가지 않고 업로드한 태그와 다른 문자로 인식하기 때문에
        //개행문자를 없애준다.
        $tag = preg_replace('/\r\n|\r|\n/', '', $newSecondSplit[0]);
        array_push($newHashTag, $tag);
    }
}
//해시태그가 배열에 들어있다면
if (count($newHashTag) != 0) {
    for ($i = 0; $i < count($newHashTag); $i++) {
        $sql = "
      SELECT*FROM hashtag
      WHERE tag='{$newHashTag[$i]}'
    ";
        $result = mysqli_query($conn, $sql);
        $size = mysqli_num_rows($result);
        //해시태그가 이미 테이블에 존재한다면
        if ($size > 0) {
            //해당 해시태그의 count만 1증가시킨다.
            $sql = "
        UPDATE hashtag
        SET count=count+1
        WHERE tag='{$newHashTag[$i]}'
      ";
            $result = mysqli_query($conn, $sql);
        } //해시태그가 존재하지 않는다면
        else {
            //새로운 태그로 hashtag테이블에 추가를 해준다.
            $sql = "
        INSERT INTO hashtag (tag)
        VALUES (
          '{$newHashTag[$i]}'
          )
      ";
            $result = mysqli_query($conn, $sql);
        }
    }
}

if (!empty($_FILES['video'])) {

//데이터 업데이트
    $sql = "
UPDATE post
SET
 article='{$article}',
 video='{$video}',
 address='{$address}',
 latitude='{$latitude}',
 longitude='{$longitude}'

 WHERE id='{$postNum}'
";

//쿼리 실행
    $result = mysqli_query($conn, $sql);


    //파일의 경로
    $save_dir = './uploadvideo/';
    //비디오 파일을 tep_name의 경로(임시 경로)에서 uploadvideo경로로 이동해서 저장해준다.
    move_uploaded_file($_FILES['video']['tmp_name'], $save_dir . $video);


    if ($result === true) {
        //업로드 된 데이터를 배열에 담는다.
        $upload_data = array(
            'postNum' => $postNum,
            'account' => $account,
            'article' => $article,
            'video' => $video,
            'address' => $address,
            'latitude' => $latitude,
            'longitude' => $longitude);
        //json string으로 인코딩한 값을 클라이언트에 보내주기 위한 echo 설정
        echo json_encode($upload_data, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
    }
} else {
    //데이터 업데이트
    $sql = "
UPDATE post
SET
 article='{$article}',
 address='{$address}',
 latitude='{$latitude}',
 longitude='{$longitude}'

 WHERE id='{$postNum}'
";

//쿼리 실행
    $result = mysqli_query($conn, $sql);

    if ($result === true) {
        //업로드 된 데이터를 배열에 담는다.
        $upload_data = array(
            'postNum' => $postNum,
            'account' => $account,
            'article' => $article,
            'address' => $address,
            'latitude' => $latitude,
            'longitude' => $longitude);
        //json string으로 인코딩한 값을 클라이언트에 보내주기 위한 echo 설정
        echo json_encode($upload_data, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
    }
}


?>
