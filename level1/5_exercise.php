<?php

    const HTTP_VER = "HTTP/1.1 ";
    const URI = "/api/checkLoginAndPassword";
    const METHOD = "GET";
    const EXPECTED_CONTENT_TYPE = "application/x-www-form-urlencoded";
    const AUTH_DATABASE = "passwordsFile.txt";
    const BODY_TO_RESPONSE = '<h1 style="color:green">FOUND</h1>';
    const STUDENT_DIR = "student";
    const ANOTHER_DIR = "another";


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

    //Divide given path to folder and file(if present)
    function uriToPath($uri) {
        $path = $uri;
        $file = "";

        $folders = preg_split('/\/|\\\\/m', $uri);
        $foldersLength = count($folders) - 1;
        $isLastElementFile = preg_match('/(^\w+)\.\w+/', $folders[$foldersLength]);
       
        //Check if file present
        if ($isLastElementFile == TRUE) {
            $file = $folders[$foldersLength];
            unset($folders[$foldersLength]);
            $path = implode("/", $folders);
        }    

        return array(
            "path" => $path,
            "file" => $file
        );
    }

    //Returns content of file situated on given path, or FALSE if path is wrong
    function getFileContent($folder, $uri) {
        //If uri equals / index.html should be opened by default
        if($uri == "/") {
            $uri = "/index.html";
        }

        //Check if fodler on given path exists
        if (file_exists($folder)) {
            //Divide given path to folder and file(if present)
            $pathAndFile = uriToPath($folder . $uri);

            //Check if fodler on given path exists
            if (file_exists($pathAndFile["path"])) {
                chdir($pathAndFile["path"]);
            } else {
                return;
            }
            
            //Check if given file exists
            if (file_exists($pathAndFile["file"])) {
                $bodyToResponse = file_get_contents($pathAndFile["file"]);

                return $bodyToResponse;
            } 
        }

        return FALSE;
    }

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

            // Get host address
            $host = $headers[0];
            //Finding root directory upon host first word
            $rootDirectory = explode(".", $host[1])[0];

            switch ($rootDirectory) {
                case STUDENT_DIR:
                    //Get file content
                    $bodyToResponse = getFileContent(STUDENT_DIR, $uri);            
                    break;
                case ANOTHER_DIR:
                    //Get file content
                    $bodyToResponse = getFileContent(ANOTHER_DIR, $uri);
                    break;
                default:
                    //File not found
                    $bodyToResponse = FALSE;
                    break;
            }

            if($bodyToResponse != FALSE) {
                $statuscode = 200;
                $statusmessage = "OK";
            } else {
                $statuscode = 404;
                $statusmessage = "File not found";
                $bodyToResponse = $statusmessage;
            }

        } else {
            /*  Mathod isn`t corect 
                Generating statuscode and message about error */
            $statuscode = 405;
            $statusmessage = "Method Not Allowed";
            $bodyToResponse = $statusmessage;
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
