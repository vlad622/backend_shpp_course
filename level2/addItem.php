<?php
include 'db_connect.php';

const SQL_REQUEST = "INSERT INTO `todos_all` (text) VALUES ('?');";
const TEXT_PLUG = '?';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (getallheaders()['Content-Type'] == 'application/json;') {
        $request = file_get_contents("php://input"); 
        $decodedRequest = json_decode($request, true);
        $todoText = $decodedRequest['text'];

        $db = OpenConnection();
        $dbReqest = str_replace(TEXT_PLUG, $todoText, SQL_REQUEST);
        $error = ""; 
           
        if (mysqli_query($db, $dbReqest)) {    
            $id = mysqli_insert_id($db);
            echo returnJson($id);
        } else {
            echo "Error: " . $dbReqest . "<br>" . $db->error;
        }
    
    } else {
        #error code
    }
}    

function returnJson($id) {
    $result = ["id" => $id];

    return json_encode($result);
}

?>