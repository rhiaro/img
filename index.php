<?php
include "top.html";

$nuh = array(".", "..", ".git", ".htaccess", "end.html", "top.html", "index.php", "auth");
        
if(isset($_GET['dir']) && is_dir($_GET['dir'])){
  $cur = $_GET['dir'];
  $nuh[] = $cur.".json";
  $meta = json_decode(file_get_contents($cur."/".$cur.".json"), true);
}else{ $cur = "/var/www/"; }
var_dump($meta);
$dirs = scandir($cur);
?>
    <h2><?=$meta['as2:name']?></h2>
    <ul>
<?
foreach($dirs as $dir){
  if(!in_array($dir, $nuh)){
    if(is_dir($dir)){
  ?>
      <li><a href="?dir=<?=$dir?>"><?=$dir?></a></li>
  <?php
    }else{
      // t-t-t-t-temp
      if(isset($_GET['list'])){
        ?>
          {
            "@id": "http://img.amy.gy/<?=$cur?>/<?=$dir?>",
            "name": ""
          },
        <?
      }elseif(isset($meta['hide']) && !in_array($dir, $meta['hide'])){
        ?>
        <p><img src="<?=$cur?>/<?=$dir?>" width="200px" /> <?=$dir?></p>
        <?php
      }
    }
  }
}
?>
    </ul>
<?
include "end.html";
?>
