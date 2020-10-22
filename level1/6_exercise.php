<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>
        <?php
        const PATH_TO_FILE = "./visitsCount";

        //Counts visits to this site
        function countVisits() {
            $visitsCount = 0;

            //Check saved file? if ti`s present, add +1 to value saved in file
            if (file_exists(PATH_TO_FILE)) {
                $visitsCount = file_get_contents(PATH_TO_FILE);
            } 
        
            //Saving new visits count
            file_put_contents(PATH_TO_FILE, $visitsCount + 1);
            echo $visitsCount;
        }
        
        countVisits();
        ?>
    </h1>
</body>
</html>