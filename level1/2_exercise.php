
<?php
  
  // не обращайте на эту функцию внимания 
  // она нужна для того чтобы правильно считать входные данные
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

  function parseTcpStringAsHttpRequest($string) {
      //divide string by spaces, to get method and uri
      $method_uri_array = explode(" ", $string);
      $method = $method_uri_array[0];
      $uri = $method_uri_array[1];

      //divide string by \n to find parts of header
      $headers = explode("\n", $string);
      $header = [];
      $count = count($headers);

      //iterate array of headers to divide them and add to anodher array
      //iterating from 1, cause first cell in array it is method and uri that was already added
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
  echo(json_encode($http, JSON_PRETTY_PRINT));



?>
