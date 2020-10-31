<?php
include 'db_connect.php';

const SQL_REQUEST = "SELECT * FROM `todos_all`;";

$db = OpenConnection();
$dbRequest = SQL_REQUEST;
$error = "";
$mysqliResult = mysqli_query($db, $dbRequest);
$items;

if ($mysqliResult != false ) {
    for ($i=0; $i < $mysqliResult->field_count; $i++) { 
        mysqli_data_seek ( $mysqliResult , $i );
        $items[$i] = $mysqliResult->fetch_assoc();
    }

    $result = array("items"=> $items);
    echo returnJson($items);
} else {
    echo "Error: " . $dbRequest . "<br>" . $db->error;
}

function returnJson($array) {
    $result = ["items" => $array];

    return json_encode($result);
}

?>