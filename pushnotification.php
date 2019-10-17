<?php
include("connectdatabase.php");//데이터베이스와 연결
$jsonData = json_decode(file_get_contents("php://input"), true);
//알림의 대상이 되는 사용자 계정
$account = $jsonData['account'];
//알림의 제목
$title = $jsonData['title'];
//알림의 내용
$body = $jsonData['body'];
//알림 클릭시 이동할 페이지;
$click_action = $jsonData['click_action'];
//알림 카테고리
$category = $jsonData['category'];
//알림이 발생한 시간
$time = date("Y-m-d H:i:s");

//api키
$api_key = 'AAAARH7TKPk:APA91bEl93uxuf7r3QMMAc9ZXzjn0CmyjY9WcKHRJA0kv-8sfV3FqKcITOFm7ugYZZJPe39I4A_oyDlquU81mCx_7lj5YS1ENUDetVs_C37PVQ1FWYGkZR1pFIEoEMg0BtwBbGgP9-3C';

function send_notification($token, $data){
  //request url
  $url = 'https://fcm.googleapis.com/fcm/send';
  //알림의 제목과 내용(payload를 notification으로 하면 background에서 onMessageReceived가 호출되지 않아서 payload를 data로 넘긴다)
  $msg = array(
    'title'=>$data['title'],
    'body'=>$data['body'],
    'click_action'=>$data['click_action'],
    'postNum'=>$data['postNum'],
    'category'=>$data['category'],
    'userAccount'=>$data['userAccount'],
    'profile'=>$data['profile'],
    'image'=>$data['image']
  );

  $fields = array(
    'registration_ids'=>$token,
    'data'=>$msg
  );

  $headers = array(
    'Authorization: key=AAAARH7TKPk:APA91bEl93uxuf7r3QMMAc9ZXzjn0CmyjY9WcKHRJA0kv-8sfV3FqKcITOFm7ugYZZJPe39I4A_oyDlquU81mCx_7lj5YS1ENUDetVs_C37PVQ1FWYGkZR1pFIEoEMg0BtwBbGgP9-3C',
    'Content-Type: application/json'
  );

//초기화
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));



$result = curl_exec($ch);
curl_close($ch);
return $result;
}//end of send_notification();

//클라이언트에서 넘어온 알림의 대상이 되는 사용자 array
$userList = explode('/', $account);

$sql = "
SELECT token FROM user
WHERE account='{$userList[0]}'
";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($result);
//현재 로그인을 해서 계정에 할당된 토큰값이 존재하는 경우에만 푸시 알림을 날려준다.
if(!empty($row['token'])){

  $mToken = array();
  array_push($mToken, $row['token']);
  //좋아요를 누른 경우
  if($category == 'like'){
    $userAccount = $jsonData['userAccount'];
    $postNum = $jsonData['postNum'];

    $sql = "
      SELECT*FROM user
      WHERE account='{$userAccount}'
    ";

    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($result);
    $profile = $row['image'];

    $sql = "
      SELECT*FROM post
      WHERE id='{$postNum}'
    ";

    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($result);
    $image = $row['image1'];

    $inputData = array(
      'title'=>$title,
      'body'=>$body,
      'click_action'=>$click_action,
      'postNum'=>$postNum,
      'category'=>$category,
      'userAccount'=>$userAccount,
      'profile'=>$profile,
      'image'=>$image
    );


    $sql = "
      INSERT INTO notification (post_id, account, body, is_checked, category, user_account, time)
      VALUES(
        '$postNum',
        '$account',
        '$body',
        0,
        '$category',
        '$userAccount',
        '$time'
        )
    ";
    mysqli_query($conn, $sql);

    $result = send_notification($mToken, $inputData);
    echo $result;

  }
  //댓글을 단 경우
  else if($category == 'comment'){
    $userAccount = $jsonData['userAccount'];
    $postNum = $jsonData['postNum'];
    $commentNum = $jsonData['commentNum'];


        $sql = "
          SELECT*FROM user
          WHERE account='{$userAccount}'
        ";

        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_array($result);
        $profile = $row['image'];

        $sql = "
          SELECT*FROM post
          WHERE id={$postNum}
        ";

        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_array($result);
        $image = $row['image1'];

    $inputData = array(
      'title'=>$title,
      'body'=>$body,
      'click_action'=>$click_action,
      'postNum'=>$postNum,
      'commentNum'=>$commentNum,
      'category'=>$category,
      'userAccount'=>$userAccount,
      'profile'=>$profile,
      'image'=>$image
    );


    $sql = "
      INSERT INTO notification (post_id, comment_id, account, body, is_checked, category, user_account, time)
      VALUES(
        '$postNum',
        '$commentNum',
        '$account',
        '$body',
        0,
        '$category',
        '$userAccount',
        '$time'
        )
    ";
    mysqli_query($conn, $sql);

    $result = send_notification($mToken, $inputData);
    echo $result;
  }
  //답글(대댓글)을 단 경우
  else if($category == 'childcomment'){
    $userAccount = $jsonData['userAccount'];//알림 송신자
    $postNum = $jsonData['postNum'];//댓글의 부모 게시물
    $commentNum = (int)$jsonData['commentNum'];//대댓글의 부모 댓글


        $sql = "
          SELECT*FROM user
          WHERE account='{$userAccount}'
        ";

        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_array($result);
        $profile = $row['image'];

        $sql = "
          SELECT*FROM post
          WHERE id={$postNum}
        ";

        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_array($result);
        $image = $row['image1'];

    $user_List = array();//알림을 받아야 하는 사용자를 추가할 배열

    $sql = "
      SELECT*FROM comment
      WHERE id={$commentNum}
    ";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($result);
    //부모 댓글 작성자와 대댓글을 작성한 사용자가 다른 경우 부모 댓글 작성자도 알림 리스트에 포함시킨다.
    if($userAccount != $row['account']){
      array_push($user_List, $row['account']);
    }

    $sql = "
      SELECT*FROM childcomment
      WHERE comment_id={$commentNum}
    ";
    $result = mysqli_query($conn, $sql);

    //전체 대댓글 중에서 대댓글을 방금 단 사용자와 다른 사용자는 모두 알림 리스트에 추가해준다.
    while($row = mysqli_fetch_array($result)){
      //알림 송신자와 대댓글의 사용자가 다르면서 배열에 대댓글 사용자가 존재하지 않는 경우에만 새롭게 배열에 사용자로 추가해준다.
      if($userAccount != $row['account'] && !in_array($row['account'], $user_List)){
        array_push($user_List, $row['account']);
      }
    }

    //알림의 대상이 되는 사용자의 단말기 토큰을 담을 배열
    $mToken = array();
    //알림의 대상이 되는 사용자 수만큼 반복문을 통해서 알림 테이블에 데이터를 넣어주고
    //토큰을 토큰 배열에 넣어줘서 해당 토큰이 할당된 단말에 모두 push알림을 해준다.
    for($i = 0; $i < count($user_List); $i++){
      $sql = "
        INSERT INTO notification (post_id, comment_id, account, body, is_checked, category, user_account, time)
        VALUES(
          '$postNum',
          '$commentNum',
          '$user_List[$i]',
          '$body',
          0,
          '$category',
          '$userAccount',
          '$time'
          )
      ";
      mysqli_query($conn, $sql);

      $sql = "
      SELECT token FROM user
      WHERE account='{$user_List[$i]}'
      ";
      $result = mysqli_query($conn, $sql);
      $row = mysqli_fetch_array($result);
      //해당 사용자의 데이터에 토큰이 존재하는 경우에만 배열에 토큰 값을 추가해준다.
      if(!empty($row['token'])){
        array_push($mToken, $row['token']);
      }

    }

    $inputData = array(
      'title'=>$title,
      'body'=>$body,
      'click_action'=>$click_action,
      'postNum'=>$postNum,
      'commentNum'=>$commentNum,
      'category'=>$category,
      'userAccount'=>$userAccount,
      'profile'=>$profile,
      'image'=>$image
    );

    $result = send_notification($mToken, $inputData);
    echo $result;
  }
  //팔로우를 한 경우
  else {
    $userAccount = $jsonData['userAccount'];

        $sql = "
          SELECT*FROM user
          WHERE account='{$userAccount}'
        ";

        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_array($result);
        $profile = $row['image'];


    $inputData = array(
      'title'=>$title,
      'body'=>$body,
      'click_action'=>$click_action,
      'category'=>$category,
      'userAccount'=>$userAccount,
      'profile'=>$profile
    );
    $sql = "
      INSERT INTO notification (account, body, is_checked, category, user_account, time)
      VALUES(
        '$account',
        '$body',
        0,
        '$category',
        '$userAccount',
        '$time'
        )
    ";
    mysqli_query($conn, $sql);

    $result = send_notification($mToken, $inputData);
    echo $result;
  }

}

 ?>
