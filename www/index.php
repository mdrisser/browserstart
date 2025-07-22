<?php
require_once("inc/config.php");
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Browser Start Page</title>
        <!-- Spectre.css CDN links for core, experimental, and icons -->
        <link rel="stylesheet" href="https://unpkg.com/spectre.css/dist/spectre.min.css" />
        <link rel="stylesheet" href="https://unpkg.com/spectre.css/dist/spectre-exp.min.css" />
        <link rel="stylesheet" href="https://unpkg.com/spectre.css/dist/spectre-icons.min.css" />
        <link rel="stylesheet" href="css/startpage.css" />
    </head>
    <body>
        <a name="top"></a>
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
        <!-- WEATHER -->
            <div id="weather" class="columns">
                
            </div>
        <!-- RSS FEEDS -->
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
                                    <i class="float-right c-hand icon icon-2x icon-arrow-down section-toggle"></i>
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
                    <br />
                    <a href="#top">Back to Top</a>
                </div>
            <?php endforeach; ?>
        </div>
    <!-- JAVASCRIPT -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script>
        const base_url = 'http://api.weatherapi.com/v1/current.json?key=';
        const apikey = '';
        const wx_url = base_url + apikey + '&q=';
        const bhc = '86442';
        const kmn = '86409';
        const prs = '86301';
        const flg = '86001';

        function get_wx(el, $url, $loc) {
            $.getJSON($url + $loc, function(data) {
                let html = "<div class='column col-3 wx'>";
                html += "<div><a href='weather.html?q="+$loc+"'>" + data['location']['name'] + " WX CONDX</a></div>";
                html += "<div class='h2 temp'>" + Math.round(data['current']['temp_f']) + "&deg;F</div>";
                html += "<div class='wind-speed'>Wind: " + Math.round(data['current']['wind_mph']) + "mph (" + data['current']['wind_dir'] + ")</div>";
                html += "<div class='wind-gust'>Gust: " + Math.round(data['current']['gust_mph']) +"mph</div>";
                html += "<div class='humidity'>Humidity: " + Math.round(data['current']['humidity']) + "%</div>";

                $(el).append(html);
            });
        }

        $(document).ready(function() {
            $('.card-body').each(function() {
                $(this).slideToggle("fast");
            });

            $('.section-toggle').click(function() {
                $(this).parent().siblings('.card-body').slideToggle('slow');

                if($(this).hasClass('icon-arrow-down')) {
                    $(this).removeClass('icon-arrow-down').addClass('icon-arrow-up');
                } else {
                    $(this).removeClass('icon-arrow-up').addClass('icon-arrow-down');
                }
            });

            get_wx('#weather', wx_url, bhc);
            get_wx('#weather', wx_url, kmn);
            get_wx('#weather', wx_url, prs);
            get_wx('#weather', wx_url, flg);
        });
        </script>
    </body>
</html>
