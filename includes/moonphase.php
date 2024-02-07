<?php
//Header("Content-type: image/png");

include 'includes.php';
 
$w = 600;
$h = 600;

$img = ImageCreate($w, $h);
$black = ImageColorAllocate($img, 0, 0, 0);
ImageFill($img, 0, 0, $black);

if (isset($_GET['date']))
  $JD = $_GET['date'];
else $JD = julianDateTime(date('d'), date('m'), date('Y'), date('H'), date('i'), date('s'));

moonPhase($img, $JD, $w, $h);

//ImagePNG($img);
//ImageDestroy($img);

function moonPhase($img, $JD, $w, $h) {
  $dorusta = true;
  $obj = new Objects($JD);
  $f = $obj->moonPhase();
  
  $im = imagecreatefrompng("moon.png");
  // stretch png do canvasu
  imagecopyresampled($img, $im, 0, 0, 0, 0, $w, $h, 600, 606);
  
  if ($f >= 0 && $f <= 0.5) {
    $mask = imagecreatetruecolor($w, $h);  
    imagealphablending($mask, true);  
  
    $mask_black = imagecolorallocate($mask, 0, 0, 0);  
    $mask_magicpink = imagecolorallocate($mask, 1, 0, 1);  
    imagecolortransparent($mask, $mask_black);  
    imagefill($mask, 0, 0, $mask_magicpink);
  } else {
    $black = ImageColorAllocate($img, 0, 0, 0);
  }

  $px = $w * 0.5;
  $py = $h * 0.50667;
  $rx = $w * 0.887;
  $ry = $h * 0.887;

  // první čtvrť až úplněk
  if ($f >= 0 && $f <= 0.25) {
    ImageFilledArc($mask, $px, $py, $rx * 4 * $f, $ry, 90, 270, $mask_black, IMG_ARC_PIE);
    ImageFilledArc($mask, $px, $py, $rx, $ry, 270, 90, $mask_black, IMG_ARC_PIE);
  }
  // úplněk až poslední čtvrť
  if ($f > 0.25 && $f <= 0.5) {
    ImageFilledArc($mask, $px, $py, $rx, $ry, 90, 270, $mask_black, IMG_ARC_PIE);
    ImageFilledArc($mask, $px, $py, $rx - 4 * ($f - 0.25) * $rx, $ry, 270, 90, $mask_black, IMG_ARC_PIE);
  }
  // poslední čtvrť až nov
  if ($f > 0.5 && $f <= 0.75) {
    ImageFilledArc($img, $px, $py, 4 * ($f - 0.5) * $rx, $ry, 90, 270, $black, IMG_ARC_PIE);
    ImageFilledArc($img, $px, $py, $rx, $ry, 270, 90, $black, IMG_ARC_PIE);
  }
  // nov až první čtvrť
  if ($f > 0.75 && $f <= 1) {
    ImageFilledArc($img, $px, $py, $rx, $ry, 90, 270, $black, IMG_ARC_PIE);
    ImageFilledArc($img, $px, $py, $rx - 4 * ($f - 0.75) * $rx, $ry, 270, 90, $black, IMG_ARC_PIE);
  }

  if ($f >= 0 && $f <= 0.5) {
    imagecopymerge($img, $mask, 0, 0, 0, 0, $w, $h, 100);
  }  
}
?>
