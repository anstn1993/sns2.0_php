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

    if( ($_SERVER['REQUEST_METHOD'] == 'POST') && isset($_POST['submit']) || $android)
    {
        //안드로이드의 postParameters변수에 입력된 이름으로 값을 받는다.
        $name=$_POST['name'];
        $country=$_POST['country'];

        //입력받지 못한 항목이 있다면 다음의 에러 메세지 생성
        if(empty($name)){
            $errMSG = "이름을 입력하세요.";
        }
        else if(empty($country)){
            $errMSG = "나라를 입력하세요.";
        }

        //에러 메세지가 없다면 이름과 나라 모두 입력된 경우
        if(!isset($errMSG))
        {
            try{

                //쿼리문을 통해서 테이블에 값 저장
                $stmt = $con->prepare('INSERT INTO person(name, country) VALUES(:name, :country)');
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':country', $country);

                //sql실행 결과를 위한 메세지 형성
                if($stmt->execute())
                {
                    $successMSG = "새로운 사용자를 추가했습니다.";
                }
                else
                {
                    $errMSG = "사용자 추가 에러";
                }

            } catch(PDOException $e) {
                die("Database error: " . $e->getMessage());
            }
        }

    }
?>

<html>
   <body>
        <?php
        if (isset($errMSG)) echo $errMSG;
        if (isset($successMSG)) echo $successMSG;

        $android = strpos($_SERVER['HTTP_USER_AGENT'], "Android");

        if(!$android)
        {
        ?>

        <form action="<?php $_PHP_SELF ?>" method="POST">
            Name: <input type = "text" name = "name" />
            Country: <input type = "text" name = "country" />
            <input type = "submit" name = "submit" />
        </form>

        <?php
      }
         ?>
   </body>
</html>
