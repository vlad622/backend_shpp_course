<?php

    const HTTP_VER = "HTTP/1.1 ";
    const URI = "/api/checkLoginAndPassword";
    const METHOD = "POST";
    const EXPECTED_CONTENT_TYPE = "application/x-www-form-urlencoded";
    const AUTH_DATABASE = "passwordsFile.txt";
    const BODY_TO_RESPONSE = '<h1 style="color:green">FOUND</h1>';


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

    /* Processes http request, if request is correct returns HTML code,
       otherwise generates errorcode and sends it to response */
    function processHttpRequest($method, $uri, $headers, $body) {
        $statuscode = 0;
        $statusmessage = "";
        $bodyToResponse = "";
        
        //Check if request have correct method
        if ($method == METHOD) {
            $contentType = $headers[1];
            $contentLength = $headers[3];

            //Check if request have correct uri
            if ($uri == URI) {
                //Check if request have correct Content-type
                if ($contentType[1] == EXPECTED_CONTENT_TYPE ) {
                    $passwordsFile = "";
    
                    //Check if passwords file is present on server
                    if (file_exists(AUTH_DATABASE)) {
                        $passwordsFile = file_get_contents(AUTH_DATABASE);
                        
                        //Making of associated array with login and password from body
                        $chunks = array_chunk(preg_split('/(=|&)/', $body), 2);
                        $authData = array_combine(array_column($chunks, 0), array_column($chunks, 1));
                        //Making arrays with logins and passwords
                        $lines = array_chunk(preg_split('/(:)|(\s)/mD', $passwordsFile), 2); 
                        $loginArray = array_column($lines, 0);
                        $passArray = array_column($lines, 1);
                        //Iterate and compare logins and passwords with given ones
                        for ($i=0; $i < count($passArray); $i++) { 
                            if ($authData["login"] == $loginArray[$i] && $authData["password"] == $passArray[$i]) {
                                /*  Login and password found
                                    Generating success statuscode and message */
                                $statuscode = 200;
                                $statusmessage = "OK";
                                $bodyToResponse = BODY_TO_RESPONSE;
    
                                break;
                            } 
                        }
                    } else {
                        /*  Passwords file not found on server
                            Generating statuscode and message about error */
                        $statuscode = 500;
                        $statusmessage = "Internal Server Error";
                        $bodyToResponse = $statusmessage;
                    }
                } else {
                    /*  Content type not matches
                        Generating statuscode and message about error */
                    $statuscode = 415;
                    $statusmessage = "Unsupported Media Type";
                    $result = $statusmessage;
                }
            } else {
                /*  Uri not found
                    Generating statuscode and message */
                $statuscode = 404;
                $statusmessage = "Not Found";
                $result = $statusmessage;
            }
        } else {
            /*  Mathod isn`t corect 
                Generating statuscode and message about error */
            $statuscode = 405;
            $statusmessage = "Method Not Allowed";
            $result = $statusmessage;
        }

        //Making header
        $header = [
            gmdate('D, d M Y H:i:s \G\M\T', time()),
            "Apache/2.2.14 (Win32)",
            strlen($bodyToResponse),
            "Closed",
            "text/html; charset=utf-8"
        ];

        //Sending responce to user
        outputHttpResponse($statuscode, $statusmessage, $header, $bodyToResponse);
    }

    //Generates response with given statuscode message and headers
    function outputHttpResponse($statuscode, $statusmessage, $headers, $body) {
        $headerTypes = ["Date: ", "Server: ", "Content-Length: ", "Connection: ", "Content-Type: "];
        $responce = HTTP_VER .  $statuscode . " ". $statusmessage . " \n";

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
