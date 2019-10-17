<?php
include("connectdatabase.php");//데이터베이스와 연결

$jsonData = json_decode(file_get_contents("php://input"), true);//json string -> json Object


$postNum = $jsonData['postNum'];//삭제할 게시물 번호
$type = $jsonData['type'];//삭제할 게시물의 타입(이미지, 비디오)
$position = $jsonData['position'];//게시물 리사이클러뷰 상에서 삭제할 게시물의 index
//게시물을 지워주기 전에 게시물 번호를 참조하여 해당 게시물 번호로 할당되어있는 댓글 데이터를 먼저 지워준다.
$sql = "
DELETE FROM comment
WHERE post_id='{$postNum}'
";

$result = mysqli_query($conn, $sql);

//그리고 게시물에 달린 대댓글들도 삭제해준다.
$sql = "
DELETE FROM childcomment
WHERE post_id='{$postNum}'
";

$result = mysqli_query($conn, $sql);

//좋아요 테이블에서 해당 게시물 번호에 달린 데이터를 다 삭제해준다.
$sql = "
DELETE FROM likepost
WHERE post_id='{$postNum}'
";

$result = mysqli_query($conn, $sql);


//해당 게시물 id의 게시물 데이터를 조회해서 이미지, 혹은 비디오 파일명을 가져온다.
$sql = "
SELECT*FROM post
WHERE id={$postNum}
";

$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($result);

//삭제될 게시물의 게시글을 가져와서 해시태그를 지워주거나 개수를 -1해준다.
//삭제될 게시물의 게시글
$beforeArticle = $row['article'];
//해시태그를 담는 배열
$hashTag = array();
//게시글에 해시태그가 있는 경우
if (strpos($beforeArticle, '#') !== false) {
    //해시태그를 기준으로 문자열을 모두 쪼갠다.
    $firstSplit = explode('#', $beforeArticle);
    //해시태그 뒤에 공백 이후 문자열이 존재할 수 있기 때문에 해당 문자열들을 공백 기준으로 다시 쪼개준다.
    for ($i = 1; $i < count($firstSplit); $i++) {
        //#해시태그\n(\r, \r\n)문자열 같은 경우에는 개행된 문자까지 태그에 포함되는 문자로 포함하기 때문에 이를 해결하기 위해서
        //개행을 기준으로 문자열을 split해서 배열화하고 그 중 0번째 index만 진짜 태그로 가져간다.
        $non_enter_tag = preg_split('/\r\n|\r|\n/', $firstSplit[$i]);
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

if($type == "image") {//이미지 게시물인 경우

//이미지 파일 명을 변수에 담고
    $image1 = $row['image1'];
    $image2 = $row['image2'];
    $image3 = $row['image3'];
    $image4 = $row['image4'];
    $image5 = $row['image5'];
    $image6 = $row['image6'];

//이미지 파일들을 지워준다.
    if (!empty($image1)) {
        unlink('uploadimage/' . $image1);
    }

    if (!empty($image2)) {
        unlink('uploadimage/' . $image2);
    }

    if (!empty($image3)) {
        unlink('uploadimage/' . $image3);
    }

    if (!empty($image4)) {
        unlink('uploadimage/' . $image4);
    }

    if (!empty($image5)) {
        unlink('uploadimage/' . $image5);
    }

    if (!empty($image6)) {
        unlink('uploadimage/' . $image6);
    }
}
else {//비디오 게시물인 경우
    $video = $row['video'];
    unlink('uploadvideo/'.$video);
}

//이제 sql문으로 해당 게시물 데이터들을 삭제해준다.
$sql = "
DELETE FROM post
WHERE id={$postNum}
";
$result = mysqli_query($conn, $sql);

if ($result === true) {
    $responseBody = array(
        'requestType' => 'deletePost',
        'position' => (int)$position
    );
    header("Content-Type: application/json: charset = utf-8");
    echo json_encode($responseBody, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
}

?>
