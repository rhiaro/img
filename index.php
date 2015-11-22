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

include "top.html";

$root = "files";

if(isset($_GET['dir']) && $_GET['dir'] != "" && is_dir($root."/".$_GET['dir'])){
  $cur = $_GET['dir'];
  $meta = get_meta($cur);
  if(!$meta){
    // Create metadata file for first time
    $j = make_json($cur);
    $fp = fopen("files/$cur/$cur.json", 'w');
    fwrite($fp, indent($j));
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
      $listmeta = get_meta($dir);
      if($listmeta){
        $name = $listmeta['as2:name'];
        $date = $listmeta['as2:published'];
        $count = count($listmeta['as2:items']);
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

<div class="h-feed align-center" about="[:this]"
  <?if(isset($meta['@context'])):?>
    prefix="
    <?foreach($meta['@context'] as $pref => $uri):?>
      <?=$pref?>: <?=$uri?>
    <?endforeach?>
    "
  <?endif?>
  <?if(isset($meta['@type'])):?>
    typeof="
    <?foreach($meta['@type'] as $type):?>
      <?=$type?>
    <?endforeach?>
    "
  <?endif?>
  
>
  <h2 class="p-name" property="as2:name"><?=$meta['as2:name']?></h2>
  <p class="wee">Published on <time class="dt-published" datetime=<?=$meta['as2:published']?>><?=date("jS F Y H:i (T)", strtotime($meta['as2:published']))?></time> by <a class="h-card u-url" href="<?=$meta['dc:creator']['@id']?>"><?=$meta['dc:creator']['@id']?></a></p>
  <ul class="plist">
    <?foreach($meta['as2:items'] as $item):?>
      <li class="h-entry w1of1" property="as2:items"
      <?if(isset($item['@type'])):?>
        typeof="
        <?foreach($item['@type'] as $type):?>
          <?=$type?>
        <?endforeach?>
        "
      <?endif?>
      >
        <p><a class="u-url" href="<?=$item['@id']?>"><img class="u-photo" src="<?=$item['@id']?>"/></a></p>
        <p class="p-summary caption" about="<?=$item['@id']?>" property="as2:name"><?=$item['as2:name']?></p>
      </li>
    <?endforeach?>
  </ul>
</div>

<?endif?>
<?
include "end.html";
?>