<?
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function make_json($dir, $date=null, $name="Album"){
  $id = "http://img.amy.gy/".$dir;
  if(!isset($date)){
    $date = date(DATE_ATOM);
  }
  $json = array(
      "@context" => array(
           "as2" => "http://www.w3.org/ns/activitystreams#"
          ,"col" => "http://ns.jasnell.me/socialwg/"
          ,"dc" => "http://purl.org/dc/elements/1.1/"
          ,"img" => "http://img.amy.gy/v#"
        ),
      "@id" => $id,
      "@type" => array("as2:Collection", "col:Album"),
      "as2:name" => $name,
      "as2:published" => $date,
      "dc:creator" => array("@id" => "http://rhiaro.co.uk/about#me"),
      "as2:items" => array()
    );
  $files = scandir("files/".$dir);
  foreach($files as $file){
    if(!is_dir("files/".$dir."/".$file)){
      $json["as2:items"][] = array("@id" => "http://img.amy.gy/files/".$dir."/".$file, "as2:name" => "");
    }
  }
  
  return stripslashes(json_encode($json));
}

include "top.html";

$root = "files";

if(isset($_GET['dir']) && is_dir($root."/".$_GET['dir'])){
  $cur = $_GET['dir'];
  var_dump($cur);
  $json = $root."/".$cur."/".$cur.".json";
  var_dump($json);
  if(file_exists($json)){
    // Get album metadata
    $meta = json_decode(file_get_contents($json), true);
  }else{
    // Create metadata file
    $j = make_json($cur);
    $fp = fopen($json, 'w');
    fwrite($fp, $j);
    fclose($fp);
  }
}else{
  // List all albums
  $cur = "/var/www/".$root;
  var_dump($cur);
  $dirs = scandir($cur);
  echo "<ul>";
  foreach($dirs as $dir){
    if(is_dir($dir) && $dir != "." && $dir != ".." && $dir != "auth" && $dir != ".git"){
      echo "<li><a href=\"$dir\">$dir</a></li>";
    }
  }
  echo "</ul>";
}
?>

<?if(isset($meta)):?>
  <h2><?=$meta['as2:name']?></h2>
  <ul>
    <?foreach($meta['as2:items'] as $item):?>
      <?if(!in_array($item['@id'], $hidden['items'])):?>
        <li>
          <p><img src="<?=$item['@id']?>" width="200px" /></p>
          <p><?=$item['as2:name']?> (<?=$item['@id']?>)</p>
        </li>
      <?endif?>
    <?endforeach?>
  </ul>
<?endif?>
<?
include "end.html";
?>