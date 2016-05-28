<?
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'helpers.php';

function make_json($dir, $date=null, $name="Album"){
  $id = "http://img.amy.gy/".$dir;
  if(!isset($date)){
    $date = date(DATE_ATOM);
  }
  $json = array(
      "@context" => array("http://www.w3.org/ns/activitystreams#",
        array(
             "id" => "id"
            ,"type" => "type"
            ,"col" => "http://ns.jasnell.me/socialwg#"
            ,"dc" => "http://purl.org/dc/elements/1.1/"
            ,"img" => "http://img.amy.gy/v#"
          )),
      "id" => $id,
      "type" => array("Collection", "col:Album"),
      "name" => $name,
      "published" => $date,
      "dc:creator" => array("id" => "http://rhiaro.co.uk/about#me"),
      "items" => array()
    );
  $files = scandir("files/".$dir);
  foreach($files as $file){
    if(!is_dir("files/".$dir."/".$file)){
      $json["items"][] = array("id" => "http://img.amy.gy/files/".$dir."/".$file, "name" => "");
    }
  }
  return json_encode_pretty($json);
}

function get_meta($dir){
  $json = "files/$dir/$dir.json";
  if(file_exists($json)){
    return json_decode(file_get_contents($json), true);
  }else{
    return false;
  }
}

$root = "files";
$errors = array();

if(isset($_GET['dir']) && $_GET['dir'] != "" && is_dir($root."/".$_GET['dir'])){
  $cur = $_GET['dir'];
  $meta = get_meta($cur);
  if(!$meta){
    // Create metadata file for first time
    $j = make_json($cur);
    $fp = @fopen("files/$cur/$cur.json", 'w');
    if($fp){
      fwrite($fp, indent($j));
      fclose($fp);
    }else{
      $errors[] = "Could not write metadata, permission denied.";
    }
    $meta = json_decode($j,true);
  }
}else{
  // List all directories
  $cur = "/var/www/".$root."/";
  $dirs = scandir($cur);
  $listout = "<ul>";
  foreach($dirs as $dir){
    if(is_dir($root."/".$dir) && $dir != "." && $dir != ".." && $dir != "auth" && $dir != ".git"){
      $listmeta = get_meta($dir);
      if($listmeta){
        $name = $listmeta['name'];
        $date = $listmeta['published'];
        $count = count($listmeta['items']);
        $listout .= "<li><a href=\"$dir/\">$name ($count)</a> <i>published: $date</li>";
      }else{
        $listout .= "<li><a href=\"$dir/\">$dir</a></li>";
      }
    }
  }
  $listout .= "</ul>";
}

include "top.php";
?>

<?if(isset($listout) && !isset($meta)):?>
<?=$listout?>
<?endif?>

<?if(isset($meta)):?>

<article class="h-feed align-center" about="[this:]>"
  <?if(isset($meta['type'])):?>
    typeof="
    <?foreach($meta['type'] as $type):?>
      <?=$type?>
    <?endforeach?>
    "
  <?endif?>
>
  <h2 class="p-name" property="name"><?=$meta['name']?></h2>
  <div>
    <p class="wee" property="summary">Published on <time class="dt-published" property="published" datetime=<?=$meta['published']?>><?=date("jS F Y H:i (T)", strtotime($meta['published']))?></time> by <a class="h-card u-url" property="dc:creator" href="<?=$meta['dc:creator']['id']?>"><?=$meta['dc:creator']['id']?></a></p>
    <ul class="plist" rel=items>
      <?foreach($meta['items'] as $item):?>
        <li class="h-entry w1of1"
        <?if(isset($item['type'])):?>
          typeof="
          <?foreach($item['type'] as $type):?>
            <?=$type?>
          <?endforeach?>
          "
        <?endif?>
        resource="<?=$item['id']?>" id="<?=basename($item['id'], ".jpg")?>">
          <p><img class="u-photo" src="<?=$item['id']?>"/></p>
          <div class="caption">
            <a class="left wee u-url" href="#<?=basename($item['id'], ".jpg")?>">#</a>
            <p class="p-summary p-name" about="<?=$item['id']?>" property="name"><?=$item['name']?></p>
            <?if(isset($item['tag'])):?>
              <p class="wee unpad" rel="tag">&#978;7
                <?foreach($item['tag'] as $tag):?>
                  <a href="<?=$tag['id']?>" resource="<?=$tag['id']?>" class="u-category h-card"><span property="name" class="p-name"><?=$tag['name']?></span></a>
                <?endforeach?>
              </p>
            <?endif?>
          </div>
        </li>
      <?endforeach?>
    </ul>
  </div>
</article>

<?endif?>
<?
include "end.html";
?>