=== Cache control by Cacholong ===
Contributors: cacholong, preliot
Tags: cache control, cache, caching, cacholong, pagespeed, fastcgi, nginx, nginx pagespeed, nginx fastcgi, cache, cronjob, cronjobs, schedule, WP-CLI, WP CLI, CLI, multisite, network sites
Requires at least: 4.3.1
Tested up to: 5.7
Requires PHP: 5.6
Stable tag: trunk
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Donate link: https://www.cacholong.nl/

“Cache control by Cacholong” is a cache control plugin for one or more Nginx servers. 



== Description ==

“Cache control by Cacholong” plugin automates purging of Nginx Pagespeed cache and Nginx FastCGI cache on your Nginx server(s). It is a backend plugin that is able to:

* Purge Nginx Pagespeed cache and/or Nginx FastCGI cache.
* Purge cache automatically, manually, with Wordpress cronjobs and WP-CLI.
* Purge caches on one or more servers (See hosts.json section for more details).
* Use commandline (WP-CLI) to purge caches or reset to factory settings.
* Remove id tag from stylesheet link tags, in order to allow Nginx Pagespeed to combine stylesheets.
* Allow purging of other cached items (see Settings > Cache control).
* Inform user of purge status.
* Support for single site and network sites
* Support for WP-CLI
* Support for WPML (including language negotiation type domain)

= When will it purge? =

This plugin will work with default and custom post types. It will purge on a save, regular update, quick edit update, slug change, delete and undelete. The user is informed with one or more messages.
There are a few caveats: 

* When a user is using the Gutenberg editor, purge messages will not be displayed.
* When the user uses “Quick edit” and “Updates” the post, no messages will be displayed, because there's no page refresh. Those messages will be shown on the next request. 

It is possible to purge with Wordpress cronjobs. Adjust the 'Cronjob settings' to perform a daily purge of caches.

You can also purge items manually. Go to the WordPress dashboard and navigate to:

* Settings > Cache control > purge single item
* Settings > Cache control > purge all caches
* Admin Toolbar > purge all caches

= Settings =

* File with hosts (JSON):                 File with information about one or more hosts for this Wordpress installation. See hosts.json section for more details. 
* Pagespeed optimized CSS:                Remove ID tag from all Wordpress stylesheet link tags to improve Nginx Pagespeed caching. Will only work on frontend.
* Purge settings default post types:      Select one or more options when purging a default post type. Possible to purge post url (default), home page, all connected Wordpress categories or all caches.
* Purge settings default custom type(s):  Select one or more options when purging a custom post type. Possible to purge post url (default), home page, all connected Wordpress categories or all caches.

= Cronjob Settings =

* Cronjob purging:                        Enable or disable cronjob purging. 
* Cronjob time of each day:               Enter HH:MM in 24 hour notation for cronjob time of day. Will fallback to 00:00 when wrong format is given.
* Purge caches                            Select caches to purge on given cronjob time. 

= hosts.json =

Hosts.json is a JSON file with information about one or more servers for this Wordpress installation. Remember that this file must contain information about all servers, including the
one with this plugin. Default path is: wp-content/uploads/cacholong-cache-control/hosts.json.

Here is an example of a hosts.json file:

``{"servers":
``    [
``        {"name": "server1", "ip": "127.0.0.1", "pagespeed": true, "fastcgi": false},
``        {"name": "server2", "ip": "127.0.0.2", "pagespeed": false, "fastcgi": true},
``    ]
``}

Each line contains information about one server. Parameters:

* name (string)
Name of host, something to identify this server.

* ip (string)
IP address of server or full url. Format: scheme://host:port/path

* pagespeed (bool)
Server uses Nginx Pagespeed (true) or not (false)

* fastcgi (bool)
Server user Nginx FastCGI (true) or not (false)

When there is no hosts.json or the path is invalid, this plugin assumes the following:

``{"servers":
``    [
``        {"name": "localhost", "ip": "127.0.0.1", "pagespeed": true, "fastcgi": true}
``    ]
``}

= How does it work =

“Cache control by Cacholong” empties partial or full cache for Nginx Pagespeed and FastCGI cache. Wordpress HTTP API is used to make (post) request to 
specific urls to trigger purges. Based on the http header responses this plugin determines if a purge is successful or not. The user is informed with messages, which are loaded after a purge and page refresh.

There are several settings to tweak purging, see Settings > Cache control.

= WP-CLI =

There are several commands available for the commandline with wp-cli. Commands generate text output which can be suppressed with --quiet. When an error occurs, text will always be displayed.

= WP-CLI exit codes =

Exit codes are 0 (no errors) or 1 (generic error).

= WP-CLI examples =

Purge command with all arguments:

``wp cacholong_cc purge [--cache=<cache>] [--ips=<ips>]

Purge Nginx FastCGI: 

``wp cacholong_cc purge --cache=fastcgi

Purge Nginx Pagespeed:

``wp cacholong_cc purge --cache=pagespeed

Purge all caches:

``wp cacholong_cc purge --cache=all
``wp cacholong_cc purge

Purge Nginx Pagespeed cache for ip address 127.0.0.1 and 127.0.0.2: 

``wp cacholong_cc purge --cache=pagespeed --ips=127.0.0.1,127.0.0.2

Purge all caches for ip address 127.0.0.1 and 127.0.0.2: 

``wp cacholong_cc purge --ips=127.0.0.1,127.0.0.2

Factory reset options for given site_id or current blog if no site_id is given:

``wp cacholong_cc factory_reset [--site_id=site_id]

= Debug =

Plugin will log basic purge information when WP_DEBUG is true (can be set in wp-config.php). Logs information in file wp-content/cacholong-cache-control.log.
Plugin will log wp_remote_request details when CACHOLONG_CACHE_DEBUG_HTTP_API_REQUEST is true (can be set in wp-config.php). Logs information in file wp-content/cacholong-cache-control.log.

== Installation ==

= Minimum Requirements =

* WordPress 4.3.1+
* Nginx server with fastcgi_cache enabled

= Optional Requirements =

* hosts.json file. File contains information about hosts and which caches are active. See hosts.json section for more details. When not available, plugin assumes localhost with all caches active. 

= Automatic installation =

1. Log in to your WordPress dashboard and navigate to the plugins menu.
2. Click “Add New”.
3. Search for “Cache control by Cacholong”.
4. Once you find this plugin, click “Install Now”.
5. Activate the plugin
6. Configure the plugin at Settings > Cache control

= Manual installation =

1. Log in to your WordPress dashboard and navigate to the plugins menu.
2. Click “Add New”.
3. Click “Upload Plugin”.
4. Locate zip file on your computer and click “Install Now”.
5. Activate the plugin
6. Configure the plugin at Settings > Cache control

= Updating =

Although updates are thoroughly tested, make a backup of your website (files and database) before you install an update.  



== Frequently Asked Questions ==

= Why doesn't this plugin work? =

This plugin has several requirements. Please check the minimum and optional requirements, before installing this plugin. Open a support topic if you
require additional help.

= When i use “Quick edit” to edit a post, nothing is happening! =

Although no message is displayed, the purge was activated! You can see the purge message when you refresh the page. This is a technical limitation.

= Why do my cronjobs execute later then I expect? =

Wordpress cronjobs are not the same as real cronjobs. Wordpress cronjobs will start if someone visits your website after (or on the) scheduled time. This might be later then expected. 
It is possible to disable Wordpress cronjobs and use a real cronjob that polls wp-cron.php on a regular base.

= Why does my cronjob execute immediately? =

If you enter a time of day that has already passed, the first cronjob is set to this time of day and is executed the first time the site is loaded (or wp-cron.php is called).

 = What should I do when Wordpress cronjobs are disabled? =

Wordpress cronjobs are disabled ('DISABLE_WP_CRON' = true). Please use a different type of cronjob to poll wp-cron.php on a regular base.
If cronjob cache purging fails make sure the hosts.json file is in place with the proper settings. See hosts.json section on the plugins page for more details.

== Screenshots ==

1. “Cache control by Cacholong" settings panel.

== Changelog == 

= 5.4.1 - 2021-07-16 =

* Changed - Fastcgi purge works with HTTP method GET + prefixed 'purge' in url

= 5.4.0 - 2021-06-18 =

* Added - Additional debug information (wp_remote_request details) when CACHOLONG_CACHE_DEBUG_HTTP_API_REQUEST is set in wp-config.php
* Changed - Now fastcgi purge works similar to pagespeed purge (without custom unlink code)
* Removed - Required server variable ‘CC_NGINX_FASTCGI_CACHE_PATH’
* Removed - Message: required $_SERVER[‘CC_NGINX_FASTCGI_CACHE_PATH’] is not set
* Changed - Code base maintenance

= 5.3.2 - 2021-04-15 =

* Changed - Fixed error in docs for WP-CLI commands

= 5.3.1 - 2021-04-15 =

* Changed - Improved readability readme.txt

= 5.3.0 - 2021-04-15 =

* Added - Support for one or more ip addresses in WP CLI commands purge to override hosts.json
* Added - WP CLI commands now have aliases
* Changed - Updated and tweaked readme.txt
* Changed - WP CLI commands purge and purge all now only work with arguments
* Changed - Removed WP CLI purge_all command (this is now handled with purge all)
* Changed - Updated copyright from 2015-2020 to 2015-2021
* Changed - Removed register_deactivation_hook hook and empty method
* Fixed - Trashing a post or page didn't purge

= 5.2.0 - 2021-03-10 =

* Fixed - Purge old and new slug when slug changes
* Fixed - Trash and untrash work again
* Added - Support for WPML (including language negotiation type domain)
* Added - Plugin is tested with Wordpress 5.7 (RC 2)

= 5.1.1 - 2020-12-14 =

* Fixed - Editing home page did not purge home page. Issue with Wordpress method url_to_postid() which does return post id for home page.

= 5.1.0 - 2020-11-18 =

* Added - WP CLI commands: purge, purge_all, factory_reset. For details see readme.txt
* Added - New admin bar commands: purge pagespeed, purge fastcgi
* Added - link to settings page on plugin index page
* Added - Plugin is tested for usage with Wordpress 5.6 RC (and 5.5.1)
* Changed - Updated copyright from 2015-2019 to 2015-2020
* Changed - Moved logging to cacholong-cache-control.log
* Fixed - Checkbox 'all caches' on settings page > Purge settings default/custom post types did not empty other checkboxes anymore. Fixed.
* Fixed - Trashing an item in Gutenberg didn't trigger purge.
* Fixed - Stripping id tags now only works on frontend and if a user is not seeing an admin page or admin_bar.

= 5.0.1 - 2020-09-30 =

* Fixed - Removing id tag for pagespeed optimizing of css, caused issues with some backend plugins. Stripping id tags now only works on frontend.

= 5.0.0 - 2019-11-13 =

* Added - Multisite support
* Added - WP CLI support
* Added - Factory reset
* Added - Message when required $_SERVER[‘CC_NGINX_FASTCGI_CACHE_PATH’] is not set
* Added - Message when all caches are disabled in hosts.json
* Added - Plugin is tested for usage with Wordpress 5.3.

See changelog.txt for all versions.

== Upgrade Notice ==

= 4.0.0 - 2018-01-26 =

* Must deinstall older version when installing version 4.0.0, because of breaking changes. Make sure you make backups of your files and database before you proceed.