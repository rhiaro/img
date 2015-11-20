<?
header("Content-Type: application/activity+json");
$root = "files";
if(isset($_GET['dir'])){
  $cur = $_GET['dir'];
  $json = $root."/".$cur."/".$cur.".json";
  if(file_exists($json)){
    echo file_get_contents($json);
  }
}
?>