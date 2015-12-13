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
          ,"col" => "http://ns.jasnell.me/socialwg#"
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

function indent($json) {

    $result      = '';
    $pos         = 0;
    $strLen      = strlen($json);
    $indentStr   = '  ';
    $newLine     = "\n";
    $prevChar    = '';
    $outOfQuotes = true;

    for ($i=0; $i<=$strLen; $i++) {

        // Grab the next character in the string.
        $char = substr($json, $i, 1);

        // Are we inside a quoted string?
        if ($char == '"' && $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;

        // If this character is the end of an element,
        // output a new line and indent the next line.
        } else if(($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            $pos --;
            for ($j=0; $j<$pos; $j++) {
                $result .= $indentStr;
            }
        }

        // Add the character to the result string.
        $result .= $char;

        // If the last character was the beginning of an element,
        // output a new line and indent the next line.
        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
            $result .= $newLine;
            if ($char == '{' || $char == '[') {
                $pos ++;
            }

            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }

        $prevChar = $char;
    }

    return $result;
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
        $name = $listmeta['as2:name'];
        $date = $listmeta['as2:published'];
        $count = count($listmeta['as2:items']);
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
  <?if(isset($meta['@type'])):?>
    typeof="
    <?foreach($meta['@type'] as $type):?>
      <?=$type?>
    <?endforeach?>
    "
  <?endif?>
>
  <h2 class="p-name" property="as2:name"><?=$meta['as2:name']?></h2>
  <div>
    <p class="wee" property="as2:summary">Published on <time class="dt-published" property="as2:published" datetime=<?=$meta['as2:published']?>><?=date("jS F Y H:i (T)", strtotime($meta['as2:published']))?></time> by <a class="h-card u-url" property="dc:creator" href="<?=$meta['dc:creator']['@id']?>"><?=$meta['dc:creator']['@id']?></a></p>
    <ul class="plist" rel=as2:items>
      <?foreach($meta['as2:items'] as $item):?>
        <li class="h-entry w1of1"
        <?if(isset($item['@type'])):?>
          typeof="
          <?foreach($item['@type'] as $type):?>
            <?=$type?>
          <?endforeach?>
          "
        <?endif?>
        resource="<?=$item['@id']?>" id="<?=basename($item['@id'], ".jpg")?>">
          <p><img class="u-photo" src="<?=$item['@id']?>"/></p>
          <div class="caption">
            <a class="left wee u-url" href="<?=$_SERVER['SERVER_NAME'].basename($item['@id'], ".jpg")?>">#</a>
            <p class="p-summary" about="<?=$item['@id']?>" property="as2:name"><?=$item['as2:name']?></p>
            <?if(isset($item['as2:tag'])):?>
              <p class="wee unpad" rel="as2:tag">&#978;7
                <?foreach($item['as2:tag'] as $tag):?>
                  <a href="<?=$tag['@id']?>" resource="<?=$tag['@id']?>"><span property="as2:name"><?=$tag['as2:name']?></span></a>
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