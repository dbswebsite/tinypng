<?php
//ARCHIVE AT https://github.com/tinify/tinify-php/archive/master.zip
require_once("lib/Tinify/Exception.php");
require_once("lib/Tinify/ResultMeta.php");
require_once("lib/Tinify/Result.php");
require_once("lib/Tinify/Source.php");
require_once("lib/Tinify/Client.php");
require_once("lib/Tinify.php");
\Tinify\setKey("YOUR_API_KEY");;
 
$dir = 'dir/';
$final_dir = 'final_dir/';
$images = scandir($dir);
$images = array_diff($images, array('.', '..'));
 
foreach ($images as $image) {
    $source = \Tinify\fromFile($dir.$image);
    $source->toFile($final_dir.$image);
} 
echo "All images are compressed.";
?>
