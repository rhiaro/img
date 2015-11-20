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

function get_meta($dir){
  $json = "files/$dir/$dir.json";
  if(file_exists($json)){
    return json_decode(file_get_contents($json), true);
  }else{
    return false;
  }
}

include "top.html";

$root = "files";

if(isset($_GET['dir']) && $_GET['dir'] != "" && is_dir($root."/".$_GET['dir'])){
  $cur = $_GET['dir'];
  $meta = get_meta($cur);
  if(!$meta){
    // Create metadata file for first time
    $j = make_json($cur);
    $fp = fopen("files/$cur/$cur.json", 'w');
    fwrite($fp, $j);
    fclose($fp);
    $meta = json_decode($j);
  }
}else{
  // List all directories
  $cur = "/var/www/".$root."/";
  $dirs = scandir($cur);
  echo "<ul>";
  foreach($dirs as $dir){
    if(is_dir($root."/".$dir) && $dir != "." && $dir != ".." && $dir != "auth" && $dir != ".git"){
      $meta = get_meta($dir);
      if($meta){
        $name = $meta['as2:name'];
        $date = $meta['as2:published'];
        $count = count($meta['as2:items']);
        echo "<li><a href=\"$dir/\">$name ($count)</a> <i>published: $date</li>";
      }else{
        echo "<li><a href=\"$dir/\">$dir</a></li>";
      }
    }
  }
  echo "</ul>";
}
?>

<?if(isset($meta)):?>
<div>
  <h2><?=$meta['as2:name']?></h2>
  <ul>
    <?foreach($meta['as2:items'] as $item):?>
      <li>
        <p><img src="<?=$item['@id']?>" width="200px" /></p>
        <p><?=$item['as2:name']?> (<?=$item['@id']?>)</p>
      </li>
    <?endforeach?>
  </ul>
</div>
<?endif?>
<?
include "end.html";
?>