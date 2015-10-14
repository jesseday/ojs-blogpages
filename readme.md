# OJS Blog Pages Plugin
================================

**This is an experimental project and very much work in progress!  It is not ready or currently intended for use on a production site.**

Todo
----

* Breakup blog_page_settings table into blog_content and blog_page_settings
* Update insert and update functions to remove blog_content from being saved to blog_page_settings table
* Support path such as blog/blog-path
* Blog view displaying *X* number of articles
* Consider needs for multilingual support?
* Consider needs for tagging support.
* Support a single cover or article image?

About
-----
This plugin is intended to provide very simple blog management.  It allows for the creation of blog content pages with the assistance of an HTML editor.

System Requirements
-------------------
Same requirements as the OJS 2.3 core.

Installation
------------
To install the plugin:
 - download and unzip the plugin to the plugins/generic directory
 - install the database schema run the following command from your OJS directory:
    $ php tools/dbXMLtoSQL.php -schema execute plugins/generic/blogPages/schema.xml
 - enable the plugin by going to:  Home > User > Journal Management > Plugin Management > Generic Plugins and selecting "ENABLE" under "Blog Pages Plugin"

Configuration
------------
New pages can be added/edited/deleted through the Plugin Management interface.
