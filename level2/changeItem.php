<?php
include 'db_connect.php';

const SQL_REQUEST = "UPDATE `todos_all` SET `text` = '?text?', `checked` = '?num?' WHERE `todos_all`.`id` = ?id?;";
const PLUGS = array('?text?', '?num?', '?id?');

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    if (getallheaders()['Content-Type'] == 'application/json;') {
        $request = file_get_contents("php://input"); 
        $decodedRequest = json_decode($request, true);
        $checked = intval($decodedRequest['checked']);

        if ($checked > 1) {
            #error code
        }
        
        $dataToChange = array($decodedRequest['text'], $checked, $decodedRequest['id']);

        $db = OpenConnection();
        $dbReqest = str_replace(PLUGS, $dataToChange, SQL_REQUEST);
        $error = ""; 
        echo $dbReqest;   

        if (mysqli_query($db, $dbReqest)) {    
            echo returnJson(true);
        } else {
            echo "Error: " . $dbReqest . "<br>" . $db->error;
        }
    
    } else {
        #error code
    }
}    

function returnJson($id) {
    $result = ["ok" => $id];

    return json_encode($result);
}

?>