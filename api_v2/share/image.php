<?php
require_once(dirname(__FILE__)."/../configdb.php");
ob_start();
$raw = isset($_GET['raw']) ? $_GET['raw'] : "";
$id = isset($_GET['id']) ? $_GET['id'] : "";
if(trim($id)=='') {
	err_page();
}
$json = base64_decode($id);
$arr = json_decode($json,true);
if(!is_array($arr) || count($arr)==0) {
	err_page();
}
if(!isset($arr['type']) || !isset($arr['player_id']) || !isset($arr['event_id'])) {
	err_page();
}

$player_id = $arr['player_id'];
$type = strtolower($arr['type']);
$event_id = $arr['event_id'];

if($type!="facebook" && $type!="twitter") {
	err_page();
}
if((!is_numeric($player_id) || $player_id<=0) && (!is_numeric($event_id) || $event_id<=0)) {
	err_page();
}
$html=sendScoreCard($event_id,$player_id,true,true,false);

// Create a200 x200 canvas image
$canvas = imagecreatetruecolor(830,920);
 
// Allocate color for rectangle
$pink = imagecolorallocate($canvas,255,255,255);
 
// Draw rectangle with its color
imagerectangle($canvas,50,50,200,150, $pink);
echo $html;
// Output and free from memory
header('Content-Type: image/jpeg');
 
imagejpeg($canvas);
imagedestroy($canvas);
die;
?>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script type='text/javascript'>
window.onload=function(){
html2canvas([document.getElementById('mydiv')], {
    onrendered: function (canvas) {
        //document.getElementById('canvas').appendChild(canvas);
        var data = canvas.toDataURL('image/jpg');
//alert(data2);
        // AJAX call to send `data` to a PHP file that creates an image from the dataURI string and saves it to a directory on the server
		$.ajax({
		  method: "POST",
		  url: "ajax_save.php?id=<?php echo $id?>",
		  data: "data="+data,
		  success:function(data){/*alert(data);*/}

		});
<?php if($raw=='1') {?>
document.getElementById('body_tag').innerHTML=data;
<?php } else {?>
        var image = new Image();
        image.src = data;
        document.getElementById('image').appendChild(image);
		document.getElementById('mydiv').innerHTML='';
		var el = document.getElementById( 'mydiv' );
		el.parentNode.removeChild( el );
<?php }?>
    }
});
}
</script>
<body id="body_tag">
  <div id="mydiv" style="width: 830px;height: 920px;background-color:#ffffff"><?php echo $html;?></div>
<?php //ob_clean();?>
    <div id="image">
    </div>
<script src="http://www.putt2gether.com/puttdemo/share/html2canvas.js"></script>

</body>