<?php

    function readHttpLikeInput() {

        $f = fopen( 'php://stdin', 'r' );
        $store = "";
        $toread = 0;
        
        while( $line = fgets( $f ) ) {
            $store .= preg_replace("/\r/", "", $line);
            if (preg_match('/Content-Length: (\d+)/',$line,$m))
                $toread=$m[1]*1;
            if ($line == "\r\n")
                break;
        }
        if ($toread > 0)
            $store .= fread($f, $toread);
        return $store;
    }

    $contents = readHttpLikeInput();

    /* Processes http request, if request is correct calculates numbers in request,
       otherwise generates errorcode and sends it to response */
    function processHttpRequest($method, $uri, $headers, $body) {
        //result of equation, if it`s present
        $result;

        //Check if request have correct method
        if ($method == "GET") {
            //Divide uri to check it correctness
            $dividedUri = explode("?", $uri);
            
            //Check if request have correct path
            if ($dividedUri[0] == "/sum") {
                //Check if request have correct query string 
                if (preg_match('/(^nums\=)/', $dividedUri[1])) {
                    //Extracting numbers from querry string
                    $argsArray = preg_split('/(^\w+\=)|(\,|\s)/', $dividedUri[1], -1, PREG_SPLIT_NO_EMPTY);
                    $result = 0;
                    $count = count($argsArray);
                    
                    //Summ of all numbers in querry string
                    for ($i = 0; $i < $count; $i++) {
                        $result += intval($argsArray[$i]);
                    }
                
                    //Generating ststuscode and message
                    $statuscode = 200;
                    $statusmessage = "OK";
                } else {
                    //Generating ststuscode and message
                    $statuscode = 406;
                    $statusmessage = "Not Acceptable";
                    $result = $statusmessage;
                }
            } else {
                //Generating ststuscode and message
                $statuscode = 404;
                $statusmessage = "Not Found";
                $result = $statusmessage;
            }    
        } else {
            //Generating ststuscode and message
            $statuscode = 400;
            $statusmessage = "Bad Request";
            $result = $statusmessage;
        }

        //Filling up header
        $header = array(
            gmdate('D, d M Y H:i:s \G\M\T', time()),
            "Apache/2.2.14 (Win32)",
            "Closed",
            "text/html; charset=utf-8",
            strlen($result)
        );

        //Sending responce to user
        outputHttpResponse($statuscode, $statusmessage, $header, $result);
    }

    //Generates response with given statuscode message and headers
    function outputHttpResponse($statuscode, $statusmessage, $headers, $body) {
        $headerTypes = array("Date: ", "Server: ", "Connection: ", "Content-Type: ", "Content-Length: ");
        $responce = "HTTP/1.1 " .  $statuscode . " ". $statusmessage . " \n";

        //Addition text of headers, to headesr names
        for ($i=0; $i < count($headers); $i++) { 
            $responce = $responce . $headerTypes[$i] . $headers[$i] . " \n";
        }  
        
        print $responce . " \n" . $body;
    }

    function parseTcpStringAsHttpRequest($string) {
        //divide string by spaces, to get method and uri 
        $method_uri_array = explode(" ", $string);
        $method = $method_uri_array[0];
        $uri = $method_uri_array[1];
  
        //divide string by \n to find parts of header
        $headers = explode("\n", $string);
        $header = [];
        $count = count($headers);
  
        /*iterate array of headers to divide them and add to anodher array
          iterating from 1, cause first cell in array it is method and uri that was already added */
        for ($i=1; $i < $count ; $i++) {
            $line = explode(": ", $headers[$i]);
  
            //check for empty line that shows end of headers
            if (count($line) != 0 and $line[0] == "") {
              break;
            } else {
              $header[] = $line;
            }
        }
  
        return array(
            "method" => $method,
            "uri" => $uri,
            "headers" => $header,
            "body" => array_pop($headers),
            
        );
    }

    $http = parseTcpStringAsHttpRequest($contents);
    processHttpRequest($http["method"], $http["uri"], $http["headers"], $http["body"]);
?>
