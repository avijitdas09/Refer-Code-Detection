<?php
$servername = "localhost";
$username = "MySQL Server Username";
$password = "Password";
$databasename = "Database Name";
$tablename = "Table Name";

header('Content-Type: application/json');
error_reporting(0);

if(!isset($_GET['method'])){
    
    echo(json_encode(json_decode('{ "status": "failed", "response_code": 400, "message": "Method not found" }'), JSON_PRETTY_PRINT));
    http_response_code(400);
    die;
}

$method = $_GET['method']."";

if ($method === "get"){
    GetReferCode();
}
elseif($method === "store"){
    
    if(!isset($_GET['refer_code'])){
        echo(json_encode(json_decode('{ "status": "failed", "response_code": 400, "message": "Refer code not found" }'), JSON_PRETTY_PRINT));
        http_response_code(400);
        die;
    }
    $refer_code = $_GET['refer_code']."";
    
    if(empty($refer_code)){
        echo(json_encode(json_decode('{"status": "failed", "response_code": 400, "message": "Refer code not found"}'), JSON_PRETTY_PRINT));
        http_response_code(400);
        die;
    }
    StoreReferCode();
}
else{
    echo(json_encode(json_decode('{ "status": "failed", "response_code": 400, "message": "Invaild method" }'), JSON_PRETTY_PRINT));
    http_response_code(400);
    die;
}

function GetReferCode(){
    
    $UserId = preg_replace('~[.:,-]~', '', GetUserIp());
    $connect = new mysqli($GLOBALS['servername'], $GLOBALS['username'], $GLOBALS['password'], $GLOBALS['databasename']);
    
    if ($connect->connect_error) {
        echo(json_encode(json_decode('{ "status": "failed", "response_code": 500, "message": "Internal server error" }'), JSON_PRETTY_PRINT));
        http_response_code(500);
        die;
    }
    $mySqlCommand = "SELECT * FROM `".$GLOBALS['tablename']."` WHERE `user_id` LIKE '$UserId'";
    $result = $connect->query($mySqlCommand);
    
    if ($result->num_rows > 0){
        
        while($row = $result->fetch_assoc()) {
           $json = array("status" => "success","response_code" => 200, "info" => array ( "refer_code" => $row["refer_code"], "time" => $row["time"] +0, "ip" => $row["ip"] ));
           echo json_encode($json, JSON_PRETTY_PRINT);
           mysqli_close($connect);
           http_response_code(200);
           die;
     }
    }
    else{
        echo(json_encode(json_decode('{ "status": "failed", "response_code": 404, "message": "Refer code not found" }'), JSON_PRETTY_PRINT));
        http_response_code(404);
        mysqli_close($connect);
        die;
    }

}
function StoreReferCode(){
    
    $UserId = preg_replace('~[.:,-]~', '', GetUserIp());
    $refer_code = $_GET['refer_code']."";
    $time = (int) round(microtime(true) * 1000);
    
    $connect = new mysqli($GLOBALS['servername'], $GLOBALS['username'], $GLOBALS['password'], $GLOBALS['databasename']);
    
    if ($connect->connect_error) {
        echo(json_encode(json_decode('{ "status": "failed", "response_code": 500, "message": "Internal server error" }'), JSON_PRETTY_PRINT));
        http_response_code(500);
        die;
    }
    
    $mySqlCommand = "SELECT * FROM `".$GLOBALS['tablename']."` WHERE `user_id` LIKE '$UserId'";
    $result = $connect->query($mySqlCommand);
    
    if ($result->num_rows > 0){
        
        $mySqlCommand = "UPDATE `".$GLOBALS['tablename']."` SET `refer_code` = '".$refer_code."', `time` = '".$time."', `ip` = '".GetUserIp()."' WHERE `".$GLOBALS['tablename']."`.`user_id` = '".$UserId."'";
        
        if ($connect->query($mySqlCommand) === TRUE) {
           echo(json_encode(json_decode('{"status": "success", "response_code": 200, "message": "Data successfully stored" }'), JSON_PRETTY_PRINT));
           mysqli_close($connect);
           http_response_code(200);
           die;
        }
        else{
            echo(json_encode(json_decode('{"status": "failed", "response_code": 500, "message": "Internal server error" }'), JSON_PRETTY_PRINT));
            http_response_code(500);
            mysqli_close($connect);
            die;
        }
    }
    else{
        
        $mySqlCommand = "INSERT INTO `".$GLOBALS['tablename']."` (`user_id`, `refer_code`, `time`, `ip`) VALUES ('".$UserId."', '".$refer_code."', '".$time."', '".GetUserIp()."')";
        
        if ($connect->query($mySqlCommand) === TRUE) {
           echo(json_encode(json_decode('{"status": "success", "response_code": 200, "message": "Data successfully stored" }'), JSON_PRETTY_PRINT));
           mysqli_close($connect);
           http_response_code(200);
           die;
        }
        else{
            echo(json_encode(json_decode('{"status": "failed", "response_code": 500, "message": "Internal server error" }'), JSON_PRETTY_PRINT));
            http_response_code(500);
            mysqli_close($connect);
            die;
        }
    }
    
}
function GetUserIp(){
    $ip = "";
    if (getenv('HTTP_CLIENT_IP'))
        $ip = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ip = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ip = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
       $ip = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ip = getenv('REMOTE_ADDR');
    else
        $ip = '0.0.0.0';
	
    return  $ip;
}

?>
