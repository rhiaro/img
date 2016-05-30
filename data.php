<?
header("Content-Type: application/activity+json");
$root = "files";
if(isset($_GET['dir'])){
  $cur = $_GET['dir'];
  $fp = explode("/", $cur);
  $fn = array_pop($fp);
  $json = "$root/$cur/$fn.json";
  if(file_exists($json)){
    echo file_get_contents($json);
  }else{
    echo "Couldn't find data in $json\n";
  }
}
?>