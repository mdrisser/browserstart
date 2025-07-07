<?php
define("DOC_ROOT", "D:/wamp64/www/start/");
define("WEB_ROOT", "/start");

require_once(DOC_ROOT."inc/vendor/autoload.php");

use Yosymfony\Toml\Toml;

$cnf = Toml::ParseFile(DOC_ROOT.'/inc/config.toml');

//print "<PRE>";
//print_r($cnf);
//print "</PRE>";

function get_feeds($category) {
    $articles = array();

    $opts = [
        "http" => [
        "header" => "User-Agent: PHP\r\n"
        ]
    ];
    $context = stream_context_create($opts);

    foreach($category['feeds'] as $feed_url) {
        try {
            $feed_xml = file_get_contents($feed_url, false, $context);
            $feed = simplexml_load_string($feed_xml);
            //$feed = simplexml_load_file($feed_url);
        } catch(Exception $e) {
            echo "URL: $feed_url\n";
            echo "Exception: $e\n";
        }

        if(!empty($feed)) {
            $i = 0;

            $site = $feed->channel->title;
            $site_link = $feed->channel->link;
            $articles['site'] = $site;
            $articles['site_link'] = $site_link;
            $articles['items'] = array();

            foreach($feed->channel->item as $item) {
                $article = [
                    "title" => $item->title,
                    "link" => $item->link,
                    "desc" => $item->description,
                    "pub_date" => date('D, d-M-YY', strtotime($item->pubDate))
                ];

                if($i>=5) break;
                $i++;
            }

            $articles['items'][] = $article;
        }
    }

    return $articles;
}
