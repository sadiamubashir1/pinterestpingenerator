<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

?><style>
        p {
            font-size: 20px;
        }
    </style>

    <p align="center">


    <form method="post">
    <input name="url" value="<?php if(isset($_REQUEST['url'])) echo $_REQUEST['url'];?>" placeholder="Enter url here..." style="width: 100%; font-size: 20px"><br>
    <input type="submit" value="Create Pinterest Image" style="font-size: 20px">
    </form>
</p>
<?php
/**
 * Created by PhpStorm.
 * User: nadeemmanzoor
 * Date: 10/11/17
 * Time: 3:00 AM
 */
require "vendor/autoload.php";
use PHPHtmlParser\Dom;
use PHPImageWorkshop\ImageWorkshop;
foreach (glob("*.jpg") as $filename) {
 unlink($filename);
}
if (isset($_POST['url'])) {

    $url = $_POST['url'];
    $html = file_get_contents($url);
    //echo ($html);
    $images = array();
    try {
        $dom = new Dom();
        $dom->load($html);
        $contents = $dom->find('a > img.wp-post-image');
        $title = $dom->find('title')[0];
        $title = $title->text;
        foreach ($contents as $image) {
            array_push($images, $image->getAttribute("src"));
        }
    } catch (\stringEncode\Exception $e) {
        echo $e->getMessage();
    }
    shuffle($images);
    if (count($images) < 4) {
        die('Not enough images to create pinterest photo');
    }
    $baseLayer = ImageWorkshop::initFromPath("base.png");
    $overLayer = ImageWorkshop::initFromPath("overlay.png");
    $leftMargin = 6;
    $topMargin = 0;
    $ind = 0;
    $pos = rand(2, 4);

    while ($topMargin < $baseLayer->getHeight()) {

        $pinguLayer = \PHPImageWorkshop\ImageWorkshop::initFromPath($images[$ind++]);
        $pinguLayer->resizeInPixel(724, null, true);
        if ($ind == $pos) {

            $thirdlayer = \PHPImageWorkshop\ImageWorkshop::initFromPath($images[$ind++]);
            $thirdlayer->resizeToFit(null, $pinguLayer->getHeight(), true);

            $doubleLayer = ImageWorkshop::initVirginLayer($thirdlayer->getWidth()+12+$pinguLayer->getWidth(), $thirdlayer->getHeight());

            $doubleLayer->addLayerOnTop($pinguLayer, 0, 0);
            $doubleLayer->addLayerOnTop($thirdlayer, $pinguLayer->getWidth()+12, 0);

            $doubleLayer->resizeInPixel(724, null, true);

            $pinguLayer = $doubleLayer;


        }

        $topMargin += 6;
        $baseLayer->addLayerOnTop($pinguLayer, $leftMargin, $topMargin);

        $topMargin += $pinguLayer->getHeight();
    }
    $baseLayer->addLayerOnTop($overLayer, 0, 0);
    $image = $baseLayer->getResult("ffffff");

    $dirPath = __DIR__;
    $filename = time() . ".jpg";
    $createFolders = true;
    $backgroundColor = null; // transparent, only for PNG (otherwise it will be white if set null)
    $imageQuality = 100; // useless for GIF, usefull for PNG and JPEG (0 to 100%)

    $baseLayer->save($dirPath, $filename, $createFolders, $backgroundColor, $imageQuality);
    ?>
    <script
        type="text/javascript"
        async defer
        src="//assets.pinterest.com/js/pinit.js"
        ></script>
    <a data-pin-do="buttonPin"
       data-pin-description = "<?php echo $title;?>"
       data-pin-url="<?php echo $url; ?>"
       data-pin-media="https://slydor.com/pinterest/<?php echo $filename; ?>"
       href="https://www.pinterest.com/pin/create/button/"
       data-pin-height="28"></a><br>
    <img src="<?php echo $filename; ?>" width="300">
<?php } else { ?>


<?php } ?>
