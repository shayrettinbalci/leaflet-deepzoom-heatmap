<?php
/**
  This file is used to slice images into pieces 256x256px to use with map (leaflet.js and deepzoom)
*/
  error_reporting(E_ALL);
  //set max execution time and memory limit
  ini_set('max_execution_time', 0);
  ini_set('display_errors', 1);
  ini_set('memory_limit', '2048M');
  if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
    $blockSizeDefault = 256;
    $blockSize = $blockSizeDefault;
    $targer_dir = "../images/";
    $filename = basename($_FILES["photo"]["name"]);
    $file = $_FILES["photo"];
    $fileType = strtolower(pathinfo($filename,PATHINFO_EXTENSION));
    $fileTmpName = $_FILES["photo"]["tmp_name"];
    $ratio = 0.5;

    // Filter imagetype
    if (!isset($file)) {
        echo 'Error!';
        exit;
    }
    if($fileType == "jpg" || $fileType == "jpeg"){
        $source = imagecreatefromjpeg($fileTmpName);
    }
    elseif ($fileType == "png") {
        $source = imagecreatefrompng($fileTmpName);
    }
    else {
        echo 'Error!';
        exit;
    }

    // Get image Size 
    $sourceSize = getimagesize($fileTmpName);

    // Create TileLayer folder with full permission
    if (!is_dir(dirname(__FILE__) . '/images/' . $filename . '/')) {
        mkdir(dirname(__FILE__) . '/images/' . $filename . '/', 777, 1);
    }

    $realsizeH = $sourceSize[0];
    $realsizeV = $sourceSize[1];

    // Get layer count
    for($i = 0; $sourceSize[0] != 1 || $sourceSize[1] != 1; $i++){ 
        if($i == 0){
            $ratio = 1;
        }else {$ratio = 0.5;}
        $sourceSize[0] = round($sourceSize[0] * $ratio);
        $sourceSize[1] = round($sourceSize[1] * $ratio);
    }

    // Return sizes original values
    $sourceSize[0] = $realsizeH; 
    $sourceSize[1] = $realsizeV;

    // Start creating TileLayer
    for($j = $i; $j != 0; $j--)
    {
        if($j == $i){
            $ratio = 1;
        }else {$ratio = 0.5;}

        $sourceSize[0] = round($sourceSize[0] * $ratio);
        $sourceSize[1] = round($sourceSize[1] * $ratio);

        $blocksH = ceil($sourceSize[0] / $blockSize);
        $blocksV = ceil($sourceSize[1] / $blockSize);
        	// Create Layer folders
            $path = dirname(__FILE__) . '/images/' . $filename . '/' . ($j - 1) . '/';
            if(!is_dir($path)){
                mkdir($path, 777, 1);
            }
            $bigimage = imagecreatetruecolor($sourceSize[0], $sourceSize[1]);
            imagecopyresampled($bigimage, $source, 0, 0, 0, 0, $sourceSize[0], $sourceSize[1], $realsizeH, $realsizeV);

            // Create Tiles as jpg
            for ($x = 0; $x < $blocksH; $x++) {
                if($x == $blocksH-1){
                    $blockSizeH = ( $sourceSize[0] - (($x) * $blockSize));
                } else {
                    $blockSizeH = $blockSizeDefault;
                }

                for ($y = 0; $y < $blocksV; $y++) {
                   if($y == $blocksV-1){
                    $blockSizeV = ( $sourceSize[1] - (($y) * $blockSize));
                } else {
                    $blockSizeV = $blockSizeDefault;
                }
                $image = imagecreatetruecolor($blockSizeH, $blockSizeV);
                imagecopyresampled($image, $bigimage, 0, 0, $blockSize * $x, $blockSize * $y, $blockSizeH, $blockSizeV, $blockSizeH, $blockSizeV);
                imagejpeg($image, $path . $x . '_'. $y . '.jpg');
                imagedestroy($image);
            }
        }
    }


    echo 'Done';
    exit;
}
?><!doctype html>
<html>
<head>
    <title>Slice images</title>
</head>
<body>
    <form action="" method="post" enctype="multipart/form-data"> 
        <p>
            Images will be placed in subdirectory ./images/ and named {column}/{cell}.{extension} in lowercase without leading zeros; numbers started with 0.<br/>
            Example: 0/0_0.jpg - 99/99_99.jpg
        </p>
        <p>
            Source image: <br/>
            <input type="file" name="photo">
        </p>
        <p>
            <button type="submit">Proceed</button>
        </p>
    </form>
</body>
</html>