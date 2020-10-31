<?php
include 'db_connect.php';

const SQL_REQUEST = "DELETE FROM `todos_all` WHERE `todos_all`.`id` = ?;";
const ID_PLUG = '?';

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    if (getallheaders()['Content-Type'] == 'application/json;') {
        $request = file_get_contents("php://input"); 
        $decodedRequest = json_decode($request, true);
        $id = $decodedRequest['id'];

        $db = OpenConnection();
        $dbRequest = str_replace(ID_PLUG, $id, SQL_REQUEST);
        $error = "";

        if (mysqli_query($db, $dbRequest)) {    
            echo returnJson(true);
        } else {
            echo "Error: " . $dbRequest . "<br>" . $db->error;
        }
    
    } else {
        #error code
    }
}    

function returnJson($bool) {
    $result = ["ok" => $bool];

    return json_encode($result);
}

?>