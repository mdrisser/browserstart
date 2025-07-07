<?php
/**
 * Simple One-Page RSS Feed Reader
 *
 * This PHP script parses RSS feeds defined in a configuration array,
 * groups them by category, and displays the latest items from each feed.
 * It uses Spectre.css for a clean, responsive design.
 *
 * NOTE: In a production environment, the feed configuration would typically
 * be loaded from a TOML, YAML, or JSON file using a dedicated parser library.
 * For this simple one-page example, the configuration is hardcoded as a PHP array.
 */

// --- RSS Feed Configuration (Simulating a TOML file) ---
// This array defines the categories and the RSS feeds within each category.
// Each feed has a 'name' (for display) and a 'url'.
$feedConfig = [
    'Astronomy' => [
        ['name' => 'Astronomy.com - PotD', 'url' => 'https://www.astronomy.com/feed/?post_type=potd'],
        ['name' => 'Astronomy.com - News', 'url' => 'https://www.astronomy.com/tags/news/feed'],
        ['name' => 'NASA - IotD', 'url' => 'https://www.nasa.gov/feeds/iotd-feed/'],
        ['name' => 'NASA - News', 'url' => 'https://www.nasa.gov/news-release/feed/'],
        ['name' => 'Sky & Telescope - News', 'url' => 'https://skyandtelescope.com/astronomy-news/feed/'],
        ['name' => 'Sky & Telescope - Observing', 'url' => 'https://skyandtelescope.com/astronomy-news/observing/feed/'],
        ['name' => 'Sky & Telescope - Sky at a Glance', 'url' => 'https://skyandtelescope.com/observing/sky-at-a-glance/feed/'],
    ],
    'Christian' => [
        ['name' => 'Christianity', 'url' => 'https://www.christianity.com/rss/'],
        ['name' => 'Christian Post - News', 'url' => 'https://www.christianpost.com/category/featured-news/rss'],
        ['name' => 'Christian Post - US', 'url' => 'https://www.christianpost.com/category/us/rss'],
        ['name' => 'Christian Post - World', 'url' => 'https://www.christianpost.com/category/world/rss'],
        ['name' => 'Christian Today - US', 'url' => 'https://www.christiantoday.com/us?format=xml'],
        ['name' => 'Christian Today - World', 'url' => 'https://www.christiantoday.com/world?format=xml'],
    ],
    'Development' => [
        ['name' => 'Smashing Magazine', 'url' => 'https://www.smashingmagazine.com/feed/'],
        ['name' => 'CSS-Tricks', 'url' => 'https://css-tricks.com/feed/'],
    ],
    'Technology' => [
        ['name' => 'TechCrunch', 'url' => 'http://feeds.feedburner.com/techcrunch/startups'],
        ['name' => 'The Verge', 'url' => 'https://www.theverge.com/rss/index.xml'],
        ['name' => 'Ars Technica', 'url' => 'https://arstechnica.com/feed/'],
        ['name' => 'NASA Aeronautics', 'url' => 'https://www.nasa.gov/aeronautics/feed/'],
    ],
];

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RSS Feed Reader</title>
    <!-- Spectre.css CDN links for core, experimental, and icons -->
    <link rel="stylesheet" href="https://unpkg.com/spectre.css/dist/spectre.min.css">
    <link rel="stylesheet" href="https://unpkg.com/spectre.css/dist/spectre-exp.min.css">
    <link rel="stylesheet" href="https://unpkg.com/spectre.css/dist/spectre-icons.min.css">
    <!-- Custom CSS for styling and responsiveness -->
    <style>
        /* Basic body styling */
        body {
            font-family: 'Inter', sans-serif; /* Preferred font */
            background-color: #f8f9fa; /* Light background */
            padding: 20px;
            line-height: 1.6;
            color: #333;
        }
        /* Main container for content */
        .container {
            max-width: 960px; /* Max width for desktop */
            margin: 0 auto; /* Center the container */
            padding: 0 15px; /* Add some horizontal padding */
        }
        /* Styling for category titles */
        .section-title {
            margin-top: 40px;
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #e6e6e6; /* Subtle separator */
            padding-bottom: 10px;
            font-size: 1.8rem;
            font-weight: bold;
        }
        /* Card styling for individual feeds */
        .card {
            margin-bottom: 20px;
            border-radius: 0.2rem;
            box-shadow: 0 0.1rem 0.2rem rgba(48,55,66,.1); /* Soft shadow */
            transition: transform 0.2s ease-in-out; /* Smooth hover effect */
        }
        .card:hover {
            transform: translateY(-3px); /* Lift card on hover */
        }
        /* Header of the card */
        .card-header {
            background-color: #f0f1f4; /* Light grey background */
            border-bottom: 1px solid #e6e6e6;
            padding: 1rem;
            border-top-left-radius: 0.2rem;
            border-top-right-radius: 0.2rem;
        }
        /* Title within the card header (feed name) */
        .card-title {
            font-size: 1.2rem;
            margin-bottom: 0;
            color: #333;
        }
        /* Subtitle within the card header (feed URL) */
        .card-subtitle {
            font-size: 0.85rem;
            color: #666;
            word-break: break-all; /* Ensure long URLs wrap */
        }
        .card-subtitle a {
            color: #666;
            text-decoration: none;
        }
        .card-subtitle a:hover {
            text-decoration: underline;
        }
        /* Body of the card (feed items) */
        .card-body {
            padding: 1rem;
        }
        /* Styling for each individual feed item (tile) */
        .tile {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px dashed #eee; /* Dashed separator between items */
        }
        .tile:last-child {
            border-bottom: none; /* No border for the last item */
            margin-bottom: 0;
            padding-bottom: 0;
        }
        /* Title of the feed item */
        .tile-title {
            font-size: 1rem;
            margin-bottom: 5px;
            line-height: 1.4;
        }
        .tile-title a {
            color: #326bfd; /* Spectre primary blue */
            text-decoration: none;
            font-weight: bold;
        }
        .tile-title a:hover {
            text-decoration: underline;
        }
        /* Subtitle of the feed item (description) */
        .tile-subtitle {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
        }
        /* Small text for publication date */
        .tile-subtitle small {
            font-size: 0.75rem;
            color: #888;
            display: block; /* Ensure it takes its own line */
            margin-top: 5px;
        }
        /* Styling for empty state messages */
        .empty-state {
            text-align: center;
            padding: 40px 0;
            color: #888;
            font-style: italic;
        }

        /* Responsive adjustments using Spectre's grid system */
        /* On medium screens (tablets), columns stack */
        @media (max-width: 960px) {
            .column.col-6 {
                width: 100%; /* Make columns full width */
            }
        }
        /* On small screens (mobile), further adjustments if needed */
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .section-title {
                font-size: 1.5rem;
            }
            .card-title {
                font-size: 1.1rem;
            }
            .tile-title {
                font-size: 0.95rem;
            }
            .tile-subtitle {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="text-center" style="margin-bottom: 40px;">
            <h1>My Browser Start Page</h1>
            <p class="text-gray">Powered by PHP and Spectre.css</p>
            <div class="nav-bar">
            <?php foreach($feedConfig as $category => $feeds): ?>
                <a class="nav-bar-item" href="#<?= $category ?>"><?= $category ?></a>
            <?php endforeach; ?>
            </div>
        </header>

        <?php
        // Loop through each defined category
        foreach ($feedConfig as $category => $feeds):
        ?>
            <a name="<?= $category ?>"></a><h2 class="section-title"><?php echo htmlspecialchars($category); ?></h2>
            <div class="columns">
                <?php
                // Loop through each feed within the current category
                foreach ($feeds as $feed):
                ?>
                    <div class="column col-12">
                        <div class="card">
                            <div class="card-header">
                                <!-- Display feed name as card title -->
                                <div class="card-title h5"><?php echo htmlspecialchars($feed['name']); ?></div>
                                <!-- Display feed URL as card subtitle, linked to the original feed -->
                                <div class="card-subtitle text-gray">
                                    <a href="<?php echo htmlspecialchars($feed['url']); ?>" target="_blank">
                                        <?php echo htmlspecialchars($feed['url']); ?>
                                    </a>
                                </div>
                                <i class="float-right c-hand icon icon-2x icon-arrow-up"></i>
                            </div>
                            <div class="card-body">
                                <?php
                                // Parse the RSS feed and get items, limited to 5
                                $items = parseRssFeed($feed['url'], 5);

                                // Check if any items were retrieved
                                if (!empty($items)):
                                    // Loop through each item and display it
                                    foreach ($items as $item):
                                ?>
                                    <div class="tile">
                                        <div class="tile-content">
                                            <!-- Item title linked to the original article -->
                                            <p class="tile-title text-bold">
                                                <a href="<?php echo htmlspecialchars($item['link']); ?>" target="_blank">
                                                    <?php echo htmlspecialchars($item['title']); ?>
                                                </a>
                                            </p>
                                            <!-- Item description -->
                                            <p class="tile-subtitle text-gray">
                                                <?php echo htmlspecialchars($item['description']); ?>
                                            </p>
                                            <!-- Publication date -->
                                            <p class="tile-subtitle text-gray">
                                                <small>Published: <?php echo htmlspecialchars($item['pubDate']); ?></small>
                                            </p>
                                        </div>
                                    </div>
                                <?php
                                    endforeach;
                                else:
                                // Display a message if no items are found or feed failed to load
                                ?>
                                    <div class="empty-state">
                                        <p>No items found or failed to load feed.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
