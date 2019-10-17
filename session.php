<?php
session_start();
 //24시간 동안 활동이 없으면 자동으로 세션의 데이터를 삭제한다. 세션 id를 삭제하는 것이 아니라 세션 데이터를 삭제하는 것.
 //세션의 지속적인 유지가 가능하려면 세션의 데이터가 있는 경우와 없는 경우를 분기해서 코딩을 하면 된다. 
ini_set("session.gc_maxlifetime", "86400");



 ?>
