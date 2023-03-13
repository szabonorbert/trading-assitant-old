<?php
    require "vendor/autoload.php";
    require "simple_html_dom.php";
    
    echo "hellawawdwdo";
    echo "<br>";

    use GuzzleHttp\Client;
    use GuzzleHttp\Cookie\CookieJar;

    //persist file cookie
    use GuzzleHttp\Cookie\FileCookieJar;
    $cookieFile = 'cookie_jar.json';
    $cookieJar = new FileCookieJar($cookieFile, TRUE);

    $client = new Client([
        'base_uri' => 'https://www.unit.network/',
        'cookies' => $cookieJar
    ]);

    $text_before = file_get_contents("saved_table.txt");


    //$response = $client->request('GET', '/t/UNIT/exchange_buy?show=history');
    $response = $client->request('GET', '/t/UNIT/exchange_buy?show=history', ['allow_redirects' => false]);
    $code = $response->getStatusCode();

   /* if ($code != 200){
        echo "vmi nem jó: " . $code;
    }*/


   $dom = str_get_html($response->getBody());
   $table = $dom -> find("#history .board__body .table__body", 0);
   foreach ($table->find(".table__row") as $row){
       echo $row->find(".table__col", 1)->innertext = "";
   }

   $text = $table->innertext;
   $text = str_replace(" ", "", $text);

/*echo $text . PHP_EOL . PHP_EOL . PHP_EOL;
echo $text_before;*/

   if ($text != $text_before){
    echo "ALERT!";
    file_put_contents("saved_table.txt", $text);
   }