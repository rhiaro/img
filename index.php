<?php
include "top.html";
$nuh = array(".", "..", ".git", ".htaccess", "end.html", "top.html");
if(isset($_GET['dir']) && is_dir($_GET['dir'])){
  $cur = $_GET['dir'];
}else{
  $cur = "/var/www/";
}
$dirs = scandir($cur);
foreach($dirs as $dir){
  if(!in_array($dir, $nuh)){
    if(is_dir($dir)){
  ?>
      <li><a href="?dir=<?=$dir?>"><?=$dir?></a></li>
  <?php
    }else{
      ?>
      <p><img src="<?=$cur?>/<?=$dir?>" width="200px" /></p>
      <?php
    }
  }
}
include "end.html";
?>
