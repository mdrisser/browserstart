<?php
define("DOC_ROOT", "/var/www/browser-start/");
define("WEB_ROOT", "/");

function debug_print($v) {
    echo "<PRE>";
    print_r($v);
    echo "</PRE>";
}

require_once(DOC_ROOT."inc/feeds.php");

$openweatherapikey = '0277487299240a0ad60731823cf0a433';
$bhc_lat = "35.10149663767291";
$bhc_long =  "-114.6048378294153";
$kmn_lat = "35.2485672786985";
$kmn_long = "-114.02866037523933";

$bhc_openweather = "https://api.openweathermap.org/data/2.5/weather?units=imperial&lat=".$bhc_lat."&lon=".$bhc_long."&appid=0277487299240a0ad60731823cf0a433";
$kmn_openweather = "https://api.openweathermap.org/data/2.5/weather?units=imperial&lat=".$kmn_lat."&lon=".$kmn_long."&appid=0277487299240a0ad60731823cf0a433";

$bhc_wx = json_decode(file_get_contents($bhc_openweather));
$kmn_wx = json_decode(file_get_contents($kmn_openweather));

//debug_print($bhc_wx);

$num_posts = 5;

/**
 * Parses an RSS feed URL and returns a limited number of items.
 *
 * @param string $url The URL of the RSS feed.
 * @param int $limit The maximum number of items to retrieve from the feed.
 * @return array An array of parsed feed items, or an empty array on failure.
 */
function parseRssFeed($url, $limit = 5) {
    $items = [];
    try {
        // Suppress warnings (@) for malformed XML or network issues to prevent page errors.
        // LIBXML_NOCDATA ensures CDATA sections are parsed as plain text.
        $xml = @simplexml_load_file($url, 'SimpleXMLElement', LIBXML_NOCDATA);

        // Check if the XML was loaded successfully.
        if ($xml === false) {
            // Log the error for debugging, but don't display it to the user directly.
            error_log("Failed to load or parse RSS feed from: " . $url);
            return []; // Return empty array if loading fails.
        }

        // Check if the feed has a 'channel' and 'item' elements (standard RSS structure).
        if (isset($xml->channel->item)) {
            $count = 0;
            foreach ($xml->channel->item as $item) {
                // Stop if the limit is reached.
                if ($count >= $limit) {
                    break;
                }

                // Extract and cast data to string to ensure consistency.
                $title = (string) $item->title;
                $link = (string) $item->link;
                $description = (string) $item->description;
                $pubDate = (string) $item->pubDate;

                // Basic sanitization and truncation for the description.
                $description = strip_tags($description); // Remove HTML tags.
                $description = html_entity_decode($description, ENT_QUOTES | ENT_HTML5, 'UTF-8'); // Decode HTML entities.
                $description = mb_strimwidth($description, 0, 200, "..."); // Truncate to 200 characters with ellipsis.

                // Format the publication date if available, otherwise 'N/A'.
                $formattedPubDate = $pubDate ? date('M d, Y H:i', strtotime($pubDate)) : 'N/A';

                $items[] = [
                    'title' => $title,
                    'link' => $link,
                    'description' => $description,
                    'pubDate' => $formattedPubDate
                ];
                $count++;
            }
        }
    } catch (Exception $e) {
        // Catch any other exceptions during parsing and log them.
        error_log("Error parsing RSS feed from " . $url . ": " . $e->getMessage());
    }
    return $items;
}
