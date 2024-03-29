<?php
include("connectdatabase.php");//데이터베이스와 연결
//클라이언트에서 넘어온 게시물 데이터를 변수에 담아준다.
//이 중에서 article, image(2,3,4,5,6), address, latitude, longitude는 null일 수도 있는데 그래도 데이터베이스에 저장되는 것에는 무관하기 때문에 그냥 둔다.
//게시물 id(번호)
$postNum = (int)$_POST['postNum'];
$account = $_POST['account'];
$article = $_POST['article'];
$image1 = $_FILES['image1']['name'];
$image2 = $_FILES['image2']['name'];
$image3 = $_FILES['image3']['name'];
$image4 = $_FILES['image4']['name'];
$image5 = $_FILES['image5']['name'];
$image6 = $_FILES['image6']['name'];
$address = $_POST['address'];
$latitude = $_POST['latitude'];
$longitude = $_POST['longitude'];



//먼저 기존에 저장되어있던 이미지파일을 다 가져온다.(파일을 삭제해주기 위해서)
$sql ="
  SELECT*FROM post
  WHERE id='{$postNum}'
";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($result);
//기존에 저장되어있던 이미지 파일 전부 삭제
if(!empty($row['image1'])){
    unlink('uploadimage/'.$row['image1']);
}

if(!empty($row['image2'])){
    unlink('uploadimage/'.$row['image2']);
}

if(!empty($row['image3'])){
    unlink('uploadimage/'.$row['image3']);
}

if(!empty($row['image4'])){
    unlink('uploadimage/'.$row['image4']);
}

if(!empty($row['image5'])){
    unlink('uploadimage/'.$row['image5']);
}

if(!empty($row['image6'])){
    unlink('uploadimage/'.$row['image6']);
}

//그리고 기존의 게시글을 가져와서 해시태그를 지워주거나 개수를 -1해준다.
$beforeArticle = $row['article'];
//기존의 해시태그를 담는 배열
$hashTag = array();
//기존 게시글에 해시태그가 있는 경우
if(strpos($beforeArticle, '#')!==false){
  //해시태그를 기준으로 문자열을 모두 쪼갠다.
  $firstSplit = explode('#', $beforeArticle);

  for($i = 1 ; $i<count($firstSplit); $i++){
    //#해시태그\n(\r, \r\n)문자열 같은 경우에는 개행된 문자까지 태그에 포함되는 문자로 포함하기 때문에 이를 해결하기 위해서
    //개행을 기준으로 문자열을 split해서 배열화하고 그 중 0번째 index만 진짜 태그로 가져간다.
    $non_enter_tag =  preg_split('/\r\n|\r|\n/',$firstSplit[$i]);
    //공백을 기준으로 한번 더 쪼개면 쪼개진 문자열 중 가장 첫번째 문자열이 해시태그가 된다.
     $secondSplit = explode(' ',$non_enter_tag[0]);
      //태그를 치고 다음 줄로 넘어가면 개행문자가 추가되는데 이게 같이 있으면 다음 줄로 넘어가지 않고 업로드한 태그와 다른 문자로 인식하기 때문에
      //개행문자를 없애준다.
      $tag = preg_replace('/\r\n|\r|\n/','',$secondSplit[0]);
      array_push($hashTag, $tag);
    }
}
//기존 게시글에 해시태그가 있다면
if(count($hashTag) != 0){
  for($i=0 ; $i<count($hashTag); $i++){
    //해당 해시태그의 태그를 검색해서
    $sql = "
      SELECT*FROM hashtag
      WHERE tag='{$hashTag[$i]}'
    ";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($result);
    //태그의 개수가 2이상이면
    if($row['count'] >= 2){
      //개수만 -1을 해준다.
      $sql = "
        UPDATE hashtag
        SET
        count = count-1
        WHERE tag ='{$hashTag[$i]}'
      ";
      $result = mysqli_query($conn, $sql);
    }
    //태그의 개수가 2미만이면
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
$newHashTag=array();
if(strpos($article, '#')!==false){
  //해시태그를 기준으로 문자열을 모두 쪼갠다.
  $newFirstSplit = explode('#', $article);
  //해시태그 뒤에 공백 이후 문자열이 존재할 수 있기 때문에 해당 문자열들을 공백 기준으로 다시 쪼개준다.
  for($i = 1 ; $i<count($newFirstSplit); $i++){
    //#해시태그\n(\r, \r\n)문자열 같은 경우에는 개행된 문자까지 태그에 포함되는 문자로 포함하기 때문에 이를 해결하기 위해서
    //개행을 기준으로 문자열을 split해서 배열화하고 그 중 0번째 index만 진짜 태그로 가져간다.
    $non_enter_tag =  preg_split('/\r\n|\r|\n/',$newFirstSplit[$i]);
    //공백을 기준으로 한번 더 쪼개면 쪼개진 문자열 중 가장 첫번째 문자열이 해시태그가 된다.
    $newSecondSplit = explode(' ', $non_enter_tag[0]);
    //태그를 치고 다음 줄로 넘어가면 개행문자가 추가되는데 이게 같이 있으면 다음 줄로 넘어가지 않고 업로드한 태그와 다른 문자로 인식하기 때문에
    //개행문자를 없애준다.
    $tag = preg_replace('/\r\n|\r|\n/','',$newSecondSplit[0]);
    array_push($newHashTag, $tag);
  }
}
//해시태그가 배열에 들어있다면
if(count($newHashTag) != 0){
  for($i = 0; $i<count($newHashTag); $i++){
    $sql = "
      SELECT*FROM hashtag
      WHERE tag='{$newHashTag[$i]}'
    ";
    $result = mysqli_query($conn, $sql);
    $size = mysqli_num_rows($result);
    //해시태그가 이미 테이블에 존재한다면
    if($size>0){
      //해당 해시태그의 count만 1증가시킨다.
      $sql = "
        UPDATE hashtag
        SET count=count+1
        WHERE tag='{$newHashTag[$i]}'
      ";
      $result = mysqli_query($conn, $sql);
    }
    //해시태그가 존재하지 않는다면
    else{
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


//데이터 업데이트
$sql = "
UPDATE post
SET
 article='{$article}',
 image1='{$image1}',
 image2='{$image2}',
 image3='{$image3}',
 image4='{$image4}',
 image5='{$image5}',
 image6='{$image6}',
 address='{$address}',
 latitude='{$latitude}',
 longitude='{$longitude}'

 WHERE id='{$postNum}'
";

//쿼리 실행
$result = mysqli_query($conn, $sql);

//회전된 이미지가 나오지 않도록 설정하기 위해서 파일 변수를 생성해서 제어한다.
$tmp_file1 = $_FILES['image1']['tmp_name'];
$tmp_file2 = $_FILES['image2']['tmp_name'];
$tmp_file3 = $_FILES['image3']['tmp_name'];
$tmp_file4 = $_FILES['image4']['tmp_name'];
$tmp_file5 = $_FILES['image5']['tmp_name'];
$tmp_file6 = $_FILES['image6']['tmp_name'];

//exif_read_data함수는 파일의 meta데이터 정보를 읽어올 수 있도록 해준다.
$exifData1 = exif_read_data($tmp_file1);
$exifData2 = exif_read_data($tmp_file2);
$exifData3 = exif_read_data($tmp_file3);
$exifData4 = exif_read_data($tmp_file4);
$exifData5 = exif_read_data($tmp_file5);
$exifData6 = exif_read_data($tmp_file6);

//jpeg형식의 파일을 만든다.
$src1 = imagecreatefromjpeg($tmp_file1);
$src2 = imagecreatefromjpeg($tmp_file2);
$src3 = imagecreatefromjpeg($tmp_file3);
$src4 = imagecreatefromjpeg($tmp_file4);
$src5 = imagecreatefromjpeg($tmp_file5);
$src6 = imagecreatefromjpeg($tmp_file6);

//이미지 1의 메타 데이터 중 회전값이 존재하는 경우
if(!empty($exifData1['Orientation'])){
  //오른쪽으로 90도가 회전된 경우
  if($exifData1['Orientation'] == 8){
    //이미지를 왼쪽으로 90도 돌려준다.
    $src1 = imagerotate($src1, 90, 0);
  }
  //오른쪽으로 180도 회전된 경우
  else if($exifData1['Orientation'] == 3){
    //이미지를 왼쪽으로 180도 돌려준다.
    $src1 = imagerotate($src1, 180, 0);
  }
  //왼쪽으로 90도 회전된 경우
  else if($exifData1['Orientation'] == 6){
    //이미지를 오른쪽으로 90도 돌려준다.
    $src1 = imagerotate($src1, -90, 0);
  }
}

  //이미지 2의 메타 데이터 중 회전값이 존재하는 경우
  if(!empty($exifData2['Orientation'])){
    //오른쪽으로 90도가 회전된 경우
    if($exifData2['Orientation'] == 8){
      //이미지를 왼쪽으로 90도 돌려준다.
      $src2 = imagerotate($src2, 90, 0);
    }
    //오른쪽으로 180도 회전된 경우
    else if($exifData2['Orientation'] == 3){
      //이미지를 왼쪽으로 180도 돌려준다.
      $src2 = imagerotate($src2, 180, 0);
    }
    //왼쪽으로 90도 회전된 경우
    else if($exifData2['Orientation'] == 6){
      //이미지를 오른쪽으로 90도 돌려준다.
      $src2 = imagerotate($src2, -90, 0);
    }
  }

    //이미지 3의 메타 데이터 중 회전값이 존재하는 경우
    if(!empty($exifData3['Orientation'])){
      //오른쪽으로 90도가 회전된 경우
      if($exifData3['Orientation'] == 8){
        //이미지를 왼쪽으로 90도 돌려준다.
        $src3 = imagerotate($src3, 90, 0);
      }
      //오른쪽으로 180도 회전된 경우
      else if($exifData3['Orientation'] == 3){
        //이미지를 왼쪽으로 180도 돌려준다.
        $src3 = imagerotate($src3, 180, 0);
      }
      //왼쪽으로 90도 회전된 경우
      else if($exifData3['Orientation'] == 6){
        //이미지를 오른쪽으로 90도 돌려준다.
        $src3 = imagerotate($src3, -90, 0);
      }
    }
      //이미지 4의 메타 데이터 중 회전값이 존재하는 경우
      if(!empty($exifData4['Orientation'])){
        //오른쪽으로 90도가 회전된 경우
        if($exifData4['Orientation'] == 8){
          //이미지를 왼쪽으로 90도 돌려준다.
          $src4 = imagerotate($src4, 90, 0);
        }
        //오른쪽으로 180도 회전된 경우
        else if($exifData4['Orientation'] == 3){
          //이미지를 왼쪽으로 180도 돌려준다.
          $src4 = imagerotate($src4, 180, 0);
        }
        //왼쪽으로 90도 회전된 경우
        else if($exifData4['Orientation'] == 6){
          //이미지를 오른쪽으로 90도 돌려준다.
          $src4 = imagerotate($src4, -90, 0);
        }
      }
        //이미지 5의 메타 데이터 중 회전값이 존재하는 경우
        if(!empty($exifData5['Orientation'])){
          //오른쪽으로 90도가 회전된 경우
          if($exifData5['Orientation'] == 8){
            //이미지를 왼쪽으로 90도 돌려준다.
            $src5 = imagerotate($src5, 90, 0);
          }
          //오른쪽으로 180도 회전된 경우
          else if($exifData5['Orientation'] == 3){
            //이미지를 왼쪽으로 180도 돌려준다.
            $src5 = imagerotate($src5, 180, 0);
          }
          //왼쪽으로 90도 회전된 경우
          else if($exifData5['Orientation'] == 6){
            //이미지를 오른쪽으로 90도 돌려준다.
            $src5 = imagerotate($src5, -90, 0);
          }
        }
          //이미지 5의 메타 데이터 중 회전값이 존재하는 경우
          if(!empty($exifData6['Orientation'])){
            //오른쪽으로 90도가 회전된 경우
            if($exifData6['Orientation'] == 8){
              //이미지를 왼쪽으로 90도를 돌려준다.
              $src6 = imagerotate($src6, 90, 0);
            }
            //오른쪽으로 180도 회전된 경우
            else if($exifData6['Orientation'] == 3){
              //이미지를 왼쪽으로 180도 돌려준다.
              $src6 = imagerotate($src6, 180, 0);
            }
            //왼쪽으로 90도 회전된 경우
            else if($exifData6['Orientation'] == 6){
              //이미지를 오른쪽으로 90도 돌려준다.
              $src6 = imagerotate($src6, -90, 0);
            }
          }

          //파일의 경로
          $save_dir='./uploadimage';
          //서버에 회전값이 반영된 이미지 파일들을 업로드한다.
          imagejpeg($src1, "$save_dir/$image1");
          imagejpeg($src2, "$save_dir/$image2");
          imagejpeg($src3, "$save_dir/$image3");
          imagejpeg($src4, "$save_dir/$image4");
          imagejpeg($src5, "$save_dir/$image5");
          imagejpeg($src6, "$save_dir/$image6");

          if($result === true){
            //업로드 된 데이터를 배열에 담는다.
            $upload_data = array(
              'postNum'=>$postNum,
              'account'=>$account,
              'article'=>$article,
              'image1'=>$image1,
              'image2'=>$image2,
              'image3'=>$image3,
              'image4'=>$image4,
              'image5'=>$image5,
              'image6'=>$image6,
              'address'=>$address,
              'latitude'=>$latitude,
              'longitude'=>$longitude);
            //json string으로 인코딩한 값을 클라이언트에 보내주기 위한 echo 설정
            echo json_encode($upload_data, JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE);
          }



 ?>
