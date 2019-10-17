<?php
include('session.php');
$account = $_POST['account'];
$name = $_POST['name'];
$nickname = $_POST['nickname'];
$email = $_POST['email'];
$introduce = $_POST['introduce'];
$imagename = $_FILES['image']['name'];
$isselected = $_POST['isselected'];
//프로필 사진이 없는 상태에서 사진을 선택하는 경우($_FILE값이 있음)
//프로필 사진이 없는 상태에서 사진을 선택하지 않는 경우($_FILE이 null)
//프로필 사진이 있는 상태에서 사진을 선택하는 경우($_FILE값이 있음)
//프로필 사진이 있는 상태에서 사진을 선택하지 않는 경우($_FILE값이 null이지만 여전히 프사는 존재하는 거임)
//프로필 사진이 있는 상태에서 사진을 지우는 경우($_FILE이 null)



//이미지가 서버로 넘어오면 사진이 회전되는 경우가 있다. 그 경우 이미지를 정방향으로 돌려서 저장해준다.

$tmp_file = $_FILES['image']['tmp_name'];
$exifData = exif_read_data($tmp_file);
$src = imagecreatefromjpeg($tmp_file);

if(!empty($exifData['Orientation'])){
  //오른쪽으로 90도가 회전된 경우
  if($exifData['Orientation'] == 8){
    $src = imagerotate($src, 90, 0);
  }
  //오른쪽으로 180도 회전된 경우
  else if($exifData['Orientation'] == 3){
    $src = imagerotate($src, 180, 0);
  }
  //왼쪽으로 90도 회전된 경우
  else if($exifData['Orientation'] == 6){
    $src = imagerotate($src, -90, 0);
  }

}



  $host = 'localhost';
  $username = 'moonsoo'; # MySQL 계정 아이디
  $userpassword = 'Rla933466r!'; # MySQL 계정 패스워드
  $dbname = 'SNS';  # DATABASE 이름

  //데이터베이스와 연결
  $conn = mysqli_connect($host, $username, $userpassword, $dbname);

  $sql="
    SELECT*FROM user
    WHERE account='{$account}'
  ";
  //쿼리 실행
  $result = mysqli_query($conn, $sql);

  $row = mysqli_fetch_array($result);
  //수정하기 전의 원래 이미지
  $ex_image = $row['image'];


//소개글과 프로필 사진을 둘 다 설정한 경우
if(!empty($introduce) && !empty($imagename)){

  //프로필 사진이 사전에 설정된 경우
  if($isselected=="yes"){
    //사용자에게 넘겨받은 아이디로 user테이블 조회
    $sql="
      UPDATE user
      SET
        name='{$name}',
        nickname='{$nickname}',
        introduce='{$introduce}',
        image='{$imagename}'
      WHERE account='{$account}'
    ";
    //쿼리 실행
    $result = mysqli_query($conn, $sql);
    //파일의 경로
    $save_dir='./profileimage';
    //서버에 사용자의 파일을 생성한다.
    // move_uploaded_file($result_file, "$save_dir/$imagename");
    imagejpeg($src, "$save_dir/$imagename");
    //이미지를 삭제해준다.
    unlink('./profileimage/'.$ex_image);

    //세션에 값을 새롭게 넣어준다.
    $_SESSION['account'] = $account;
    $_SESSION['name'] = $name;
    $_SESSION['nickname'] = $nickname;
    $_SESSION['introduce'] = $introduce;
    $_SESSION['image'] = $imagename;
  }
  //사진이 없는 상태에서 설정한 경우
  else {
    //사용자에게 넘겨받은 아이디로 user테이블 조회
    $sql="
      UPDATE user
      SET
        name='{$name}',
        nickname='{$nickname}',
        introduce='{$introduce}',
        image='{$imagename}'
      WHERE account='{$account}'
    ";
    //쿼리 실행
    $result = mysqli_query($conn, $sql);
    //파일의 경로
    $save_dir='./profileimage';
    //서버에 사용자의 파일을 생성한다.
    // move_uploaded_file($result_file, "$save_dir/$imagename");
    //param1: 이미지 파일
    //param2: 저장 경로
    imagejpeg($src, "$save_dir/$imagename");

    //세션에 값을 새롭게 넣어준다.
    $_SESSION['account'] = $account;
    $_SESSION['name'] = $name;
    $_SESSION['nickname'] = $nickname;
    $_SESSION['email'] = $email;
    $_SESSION['introduce'] = $introduce;
    $_SESSION['image'] = $imagename;
  }



}
//소개글은 비고 프로필 사진은 설정한 경우
 else if(empty($introduce) && !empty($imagename)){

     //프로필 사진이 사전에 설정된 경우
     if($isselected=="yes"){
      //사용자에게 넘겨받은 아이디로 user테이블 조회
      $sql="
        UPDATE user
        SET
          name='{$name}',
          nickname='{$nickname}',
          introduce=NULL,
          image='{$imagename}'
        WHERE account='{$account}'
      ";
      //쿼리 실행
      $result = mysqli_query($conn, $sql);

      //파일의 경로
      $save_dir='./profileimage';
      //서버에 사용자의 파일을 생성한다.
      // move_uploaded_file($result_file, "$save_dir/$imagename");
      imagejpeg($src, "$save_dir/$imagename");
      //이미지를 삭제해준다.
      unlink('./profileimage/'.$ex_image);
      //세션에 값을 새롭게 넣어준다.
      $_SESSION['account'] = $account;
      $_SESSION['name'] = $name;
      $_SESSION['nickname'] = $nickname;
      $_SESSION['email'] = $email;
      $_SESSION['introduce'] ='';
      $_SESSION['image'] = $imagename;
    }
    //사진이 없는 상태에서 설정한 경우
    else {
      //사용자에게 넘겨받은 아이디로 user테이블 조회
      $sql="
        UPDATE user
        SET
          name='{$name}',
          nickname='{$nickname}',
          introduce=NULL,
          image='{$imagename}'
        WHERE account='{$account}'
      ";
      //쿼리 실행
      $result = mysqli_query($conn, $sql);

      //파일의 경로
      $save_dir='./profileimage';
      //서버에 사용자의 파일을 생성한다.
      // move_uploaded_file($result_file, "$save_dir/$imagename");
      imagejpeg($src, "$save_dir/$imagename");

      //세션에 값을 새롭게 넣어준다.
      $_SESSION['account'] = $account;
      $_SESSION['name'] = $name;
      $_SESSION['nickname'] = $nickname;
      $_SESSION['email'] = $email;
      $_SESSION['introduce'] ='';
      $_SESSION['image'] = $imagename;
    }
}
//소개글은 작성하고 프로필 사진은 넣지 않은 경우
 else if(!empty($introduce) && empty($imagename)){
   //사진이 있는 상태에서 아무 짓도 하지 않은 경우(isselected yes)
   if($isselected == "yes"){

    //사용자에게 넘겨받은 아이디로 user테이블 조회
    $sql="
      UPDATE user
      SET
        name='{$name}',
        nickname='{$nickname}',
        introduce='{$introduce}'
      WHERE account='{$account}'
    ";
    //쿼리 실행
    $result = mysqli_query($conn, $sql);


    //세션에 값을 새롭게 넣어준다.
    $_SESSION['account'] = $account;
    $_SESSION['name'] = $name;
    $_SESSION['nickname'] = $nickname;
    $_SESSION['email'] = $email;
    $_SESSION['introduce'] = $introduce;
   }
   //사진이 없는 상태에서 아무 짓도 하지 않은 경우(isselected no)
   //사진이 있었는데 지운 경우
   else {
     //사용자에게 넘겨받은 아이디로 user테이블 조회
     $sql="
       UPDATE user
       SET
         name='{$name}',
         nickname='{$nickname}',
         introduce='{$introduce}',
         image=NULL
       WHERE account='{$account}'
     ";
     //쿼리 실행
     $result = mysqli_query($conn, $sql);

     //이미지를 삭제해준다.
     unlink('profileimage/'.$ex_image);

     //세션에 값을 새롭게 넣어준다.
     $_SESSION['account'] = $account;
     $_SESSION['name'] = $name;
     $_SESSION['nickname'] = $nickname;
     $_SESSION['email'] = $email;
     $_SESSION['introduce'] = $introduce;
     $_SESSION['image']='';
   }
}
//둘 다 빈 경우
else {
  //사진이 있는 상태에서 아무 짓도 하지 않은 경우(isselected yes)
  if($isselected == "yes"){

    //사용자에게 넘겨받은 아이디로 user테이블 조회
    $sql="
      UPDATE user
      SET
        name='{$name}',
        nickname='{$nickname}',
        introduce=NULL,
      WHERE account='{$account}'
    ";
    //쿼리 실행
    $result = mysqli_query($conn, $sql);


    //세션에 값을 새롭게 넣어준다.
    $_SESSION['account'] = $account;
    $_SESSION['name'] = $name;
    $_SESSION['nickname'] = $nickname;
    $_SESSION['email'] = $email;
    $_SESSION['introduce'] = $introduce;

  }
  //사진이 없는 상태에서 아무 짓도 하지 않은 경우(isselected no)
  //사진이 있었는데 지운 경우
  else {
    //사용자에게 넘겨받은 아이디로 user테이블 조회
    $sql="
      UPDATE user
      SET
        name='{$name}',
        nickname='{$nickname}',
        introduce=NULL,
        image=NULL
      WHERE account='{$account}'
    ";
    //쿼리 실행
    $result = mysqli_query($conn, $sql);

    //이미지를 삭제해준다.
    unlink('profileimage/'.$ex_image);

    //세션에 값을 새롭게 넣어준다.
    $_SESSION['account'] = $account;
    $_SESSION['name'] = $name;
    $_SESSION['nickname'] = $nickname;
    $_SESSION['email'] = $email;
    $_SESSION['introduce'] = $introduce;
    $_SESSION['image']='';
  }
}

//사용자 세션 데이터를 배열에 담은 후
$user_data = array('account'=>$account, 'name'=>$name, 'nickname'=>$nickname, 'email'=>$email, 'introduce'=>$introduce, 'image'=>$imagename);
//json string으로 인코딩한 값을 클라이언트에 보내주기 위한 echo 설정
echo json_encode($user_data, JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE);
 ?>
