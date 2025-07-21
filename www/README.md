# Web Pages

This section contains web pages and supporting files that should be uploaded to your web server. The pages rely on Spectre CSS and jQuery, both of which are pulled from CDNs. If your firewall is blocking access to these CDNs you will need to either download and host the files yourself, in which case you need to change the appropriate lines in index.php, new_tab.html, and weather.html, or you need to allow access to these site through your firewall, do this at your own risk, I accept no responsibility for you allowing these site through your firewall.

## INDEX.PHP

This is the page that you can set to your home page, it displays the RSS feeds that you want to see.

## NEW_TAB.HTML
This is my page for new tabs, it just gives a search box and some semi-local weather. Each brief weather summary contains a link to a more detailed look at current weather conditions for the specified (in the link) location.

***NOTE:*** You may need to use a browser extension to be able to set your new tab page.

## WEATHER.HTML

This page takes the weather location passed to it from new_tab.html and provides a more detailed look at that locations current weather conditions.

In the future I may add weather forecasts as well.