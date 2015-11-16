<?
include "top.html";

if(isset($_GET['u'])){ $u = $_GET['u']; }
if(isset($_GET['p'])){ $p = $_GET['p']; }

if(isset($_GET['dir']) && is_dir($_GET['dir'])){
  $cur = $_GET['dir'];
  // Get album metadata
  $meta = json_decode(file_get_contents($cur."/".$cur.".json"), true);
}else{
  // List all albums
  $cur = "/var/www/";
  $dirs = scandir($cur);
  echo "<ul>";
  foreach($dirs as $dir){
    if(is_dir($dir) && $dir != "." && $dir != ".." && $dir != "auth"){
      echo "<li><a href=\"?dir=$dir\">$dir</a></li>";
    }
  }
  echo "</ul>";
}
?>

<?if(isset($meta)):?>
  <h2><?=$meta['as2:name']?></h2>
  <ul>
    <?foreach($meta['as2:items'] as $item):?>
      <?if(!in_array($item, $meta['hide'])):?>
        <p><img src="<?=$item['@id']?>" width="200px" /></p>
        <p><?=$item['as2:name']?> (<?=$item['@id']?>)</p>
      <?endif?>
    <?endforeach?>
  </ul>
<?endif?>
<?
include "end.html";
?>