<!doctype html>
<html>
  <head>
    <title>Photos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="http://rhiaro.co.uk/css/normalize.min.css" />
    <link rel="stylesheet" href="http://rhiaro.co.uk/css/main.css" />
    <style type="text/css">
     html { width: 100%; }
     .plist .caption {
       background-color: black;
       opacity: 0.6;
       color: white;
       padding: 0.5em;
       font-weight: bold;
     }
     li { max-height: 100%; }
     li img { max-height: 100%; }
     @media screen and (min-width: 768px){
       .plist .caption {
         visibility: hidden;
         margin-top: -4em;
         min-height: 3em;
       }
       li:hover .caption {
         visibility: visible;
       }
     }
    </style>
  </head>
  <body
  <?if(isset($meta['@context'])):?>
    prefix="this: <?=$meta['@id']?> as: http://www.w3.org/ns/activitystreams# 
    <?foreach($meta['@context'][1] as $pref => $uri):?>
      <?=$pref?>: <?=$uri?>
    <?endforeach?>
    "
  <?endif?>
  >
    <main>
      <h1>Albums</h1>
      <p><a href="/">&lt; All</a></p>
