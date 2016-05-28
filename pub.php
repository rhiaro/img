<?
include 'helpers.php';

function verify_token($token){
  if($token == file_get_contents('token')){
    return array("me" => "http://rhiaro.co.uk/about#me", "issued_by" => "https://apps.rhiaro.co.uk", "client_id" => "https://apps.rhiaro.co.uk/", "scope" => "update");
  }else{
    $ch = curl_init("https://tokens.indieauth.com/token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, Array(
         "Content-Type: application/x-www-form-urlencoded"
        ,"Authorization: $token"
    ));
    $response = Array();
    parse_str(curl_exec($ch), $response);
    curl_close($ch);
    return $response;
  }
}

// AUTH FIRST
// Verify token
$headers = apache_request_headers();
if(isset($headers['Authorization'])) {
  $token = $headers['Authorization'];
  $response = verify_token($token);
  $me = @$response['me'];
  $iss = @$response['issued_by'];
  $client = @$response['client_id'];
  $scope = @$response['scope'];
}else{
  header("HTTP/1.1 403 Forbidden");
  echo "403: No authorization header set.";
  exit;
}

if(empty($response)){
  header("HTTP/1.1 401 Unauthorized");
  echo "401: Access token could not be verified.";
  exit;
}elseif(stripos($me, "rhiaro.co.uk") === false || $scope != "update"){
  header("HTTP/1.1 403 Forbidden");
  echo "403: Access token was not valid.";
  exit;
}else{

  if(empty($_POST)){
    $post = file_get_contents('php://input');
  }

  if(isset($post) && !empty($post)){
    
    // Store activity
    $id = date("Y-m-d_h:i:s")."_".uniqid();
    if(file_put_contents ( "logs/".$id.".json" , $post )){
      // Do update 
      $post = json_decode($post, true);

      if(!isset($post["@context"])){ $post["@context"] = "http://www.w3.org/ns/activitystreams#"; }
    
      // Find type
      if(isset($post["@type"])){
        $type = $post["@type"];
      }elseif(isset($post["type"])){
        $type = $post["type"];
      }
      // Find object
      if(isset($post["object"])){
        $object = $post["object"];
        if(isset($post["object"]["@id"])){
          $id = $post["object"]["@id"];
        }elseif(isset($post["object"]["id"])){
          $id = $post["object"]["id"];
        }
      }
      
      // Get collection object belongs to
      // ...arbitrary parsing of url, doubleplusungood :s
      $path = explode("/", str_replace("http://img.amy.gy/files/", "", $id));
      $file = array_pop($path);
      $collection = array_pop($path);
      $pathstr = "";
      if(count($path) > 0){
        $pathstr = implode("/", $path);
      }
      $jsonpath = "files/".$pathstr."/".$collection."/".$collection.".json";
      $json = json_decode(file_get_contents($jsonpath), true);
      
      // Replace object in collection
      // TODO: not hardcode items
      foreach($json['items'] as $k => $item){
        if($item["@id"] == $id){
          $json['items'][$k] = $object;
        }elseif($item["id"] == $id){
          $json['items'][$k] = $object;
        }
      }

      // Rewrite collection
      $updated = json_encode_pretty($json);
      
      if(file_put_contents($jsonpath, $updated)){
        header("HTTP/1.1 201 Created");
        echo "Resource updated";
      }else{
        header("HTTP/1.1 500 Internal Server Error");
        echo "500: Could not make update (probably a permissions issue). ";
        var_dump($jsonpath);
      }

    }else{
      header("HTTP/1.1 500 Internal Server Error");
      echo "500: Could not store activity log (probably a permissions issue).";
    }

  }else{
    header("HTTP/1.1 400 Bad Request");
    echo "400: Nothing posted";
  }
  
}

?>