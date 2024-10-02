=== Autolinks Manager - SEO Auto Linker ===
Contributors: DAEXT
Tags: automatic linking, automatic links, SEO auto linker, link building, internal links
Donate link: https://daext.com
Requires at least: 4.5
Tested up to: 6.5.4
Requires PHP: 5.3
Stable tag: 1.10.08
License: GPLv3

Applying autolinks in your website is a smart strategy to generate more visits on your pages, sell more products, earn money with a referral system or to improve your SEO.

The Autolinks Manager plugin, with its advanced autolinks system and a great number of options, gives you the ability to apply this strategy at its maximum level.

== Description ==
Applying autolinks in your website is a smart strategy to generate more visits on your pages, sell more products, earn money with a referral system or to improve your SEO.

The Autolinks Manager plugin, with its advanced autolinks system and a great number of options, gives you the ability to apply this strategy at its maximum level.

### Pro Version

For professional users, we distribute a [Pro Version](https://daext.com/autolinks-manager/) of this plugin.

### Technical Details

The total control on the application of the autolinks is what really matters in an autolink plugin.

#### Unaltered HTML
The autolinks are applied on the fly by PHP when the post are displayed in the front-end and no changes are performed on the actual HTML of the post stored in the database. So you can at anytime add, remove or modify your autolinks without negative implications for your website.

#### Custom Attributes
The HTML link elements generated with the applications of autolinks can be created with:

* Custom Title attribute to describe the linked document
* Custom Target attribute to open the linked document in a new window or tab
* Custom Nofollow attribute to instruct the search engines that the link should not influence the ranking of the link’s target

#### Affected Posts
Sometimes specific autolinks should not be applied on the entire website, but only activated with specific topics.

That’s why with this plugin for each autolink you can determine:

* In which post types the defined keyword should be automatically converted to a link
* In which categories the defined keyword should be automatically converted to a link
* In which tags the defined keyword should be automatically converted to a link
* The term group, which is a list of terms that should be compared with the ones available on the posts where the autolinks are applied

#### Advanced Match
The search for occurrences of the keyword performed by the algorithm used to apply the autolink can be tuned based on your specific needs with the following options:

* The Case Sensitive Search option to select if the defined keyword should match or not uppercase and lowercase variations
* The Left Boundary option to match keywords preceded by a generic boundary or by a specific character
* The Right Boundary option to match keywords followed by a generic boundary or by a specific character
* The Keyword Before option to match occurrences preceded by a specific string
* The Keyword After option to match occurrences followed by a specific string
* The Limit option to determine the maximum number of matches of the defined keyword automatically converted to a link
* The Priority option to determine the order used to apply the autolinks on the post

#### Test Mode
This feature, if enabled through the Test Mode plugin option, allows you to apply the autolinks on the front-end of your website only to the WordPress users that have the capability required to create and edit autolinks. So you can easily test the application of the autolinks in a production environment without actually changing the content of the posts for your visitors and for the search engines.

#### Random Prioritization
The advanced Random Prioritization option is extremely useful to randomize on a per-post basis the order used to apply the autolinks with the same priority and as a consequence to ensure a better distribution of the autolinks.

#### General Limit
A limit for the maximum number of autolinks allowed in the same post can be determined with a fixed value assigned to the General Limit (Amount) option or automatically calculated based on the length of the post and the value assigned to the General Limit (Characters per Autolink) option.

The use of the General Limit feature is recommended to limit the application of the autolinks to a reasonable amount.

#### Same URL Limit
Use this option to limit the number of autolinks with the same URL to a specified value. This option is useful when you have multiple keywords that point to the same resource and you want to limit the number of times that a resource is linked.

#### Protected Tags
With this option you can instruct the algorithm to not apply the autolinks on specific HTML tags present in your posts.

Let’s say that you don’t want to add autolinks inside the main headings, the tables and the code snippets. Simply add the list “h1, h2, h3, table, code” in the Protected Tags option and you are done.

#### Categories
The plugin includes the possibility to categorize your autolinks, this is extremely useful when you have a high number of autolinks used for different purposes.

For example you can:

* Create autolinks to convert keywords that are part of a glossary and include them in the “Wiki” category
* Create autolinks to convert keywords associated with products sold by an external website and include them in the “Referral” category
* Create autolinks to convert keywords associated with your best articles and include them in the “Internal Links” category

#### Meta Box
The Autolinks Manager meta box allows you to disable the application of the autolinks on a per-post basis. Simply visit the post where you don’t want to apply the autolinks and set to “No” the Enable Autolinks select-box available in the meta box.

#### Extremely Customizable
With the 33 general options you can control various aspects of the plugin. You can for example set the default values for the new autolinks, control how the analysis performed on the posts should be executed, control advanced aspects associated with the application of the autolinks, and more.

#### Gutenberg Ready
This plugin allows you to select exactly on which Gutenberg blocks the autolinks should be applied. So you can be very precise in the application of the autolinks and avoid any kind of issue associated with the application of autolinks on Gutenberg blocks.

#### Multisite Ready
This plugin can be used on a WordPress Network, and supports both a Network Activation (the plugin will be activated on all the sites of your WordPress Network in a single step) and a Single Site Activation (the plugin will be manually activated on single sites of the network)

#### Credits
This plugin makes use of the following resources:

* Chosen licensed under the MIT License

For each library you can find the actual copy of the license inside the folder used to store the library files.

== Installation ==
= Installation (Single Site) =

With this procedure you will be able to install the Autolinks Manager plugin on your WordPress website:

1. Visit the **Plugins -> Add New** menu
2. Click on the **Upload Plugin** button and select the zip file you just downloaded
3. Click on **Install Now**
4. Click on **Activate Plugin**

= Installation (Multisite) =

This plugin supports both a **Network Activation** (the plugin will be activated on all the sites of your WordPress Network) and a **Single Site Activation** in a **WordPress Network** environment (your plugin will be activate on single site of the network).

With this procedure you will be able to perform a **Network Activation**:

1. Visit the **Plugins -> Add New** menu
2. Click on the **Upload Plugin** button and select the zip file you just downloaded
3. Click on **Install Now**
4. Click on **Network Activate**

With this procedure you will be able to perform a **Single Site Activation** in a **WordPress Network** environment:

1. Visit the specific site of the **WordPress Network** where you want to install the plugin
2. Visit the **Plugins** menu
3. Click on the **Activate** button (just below the name of the plugin)

== Changelog ==

= 1.10.08 =

*June 14, 2024*

* The input fields of type "text" in the Autolinks, Categories, and Term Groups menu now have the proper maxlength attribute value assigned.
* The descriptions of the screenshots have been updated.

= 1.10.07 =

*June 10, 2024*

* Major back-end UI update.

= 1.10.06 =

*April 7, 2024*

* Fixed a bug (started with WordPress version 6.5) that prevented the creation of the plugin database tables and the initialization of the plugin database options during the plugin activation.

= 1.10.05 =

*November 1, 2023*

* Nonce fields have been added to the back-end menus.
* General refactoring. The phpcs "WordPress" ruleset has been partially applied to the plugin code.
* Bug fix.

= 1.10.04 =

*January 13, 2023*

* The "Protect Attributes" option has been added.
* Links to rate the plugin have been added in the back-end menus.

= 1.10.03 =

*September 6, 2022*

* The "Export to Pro" menu has been added.
* Changelog added.

= 1.10.02 =

*December 28, 2021*

* Improved internationalization.
* Updated links to resources.

= 1.10.01 =

*March 6, 2021*

* Added the load_plugin_textdomain() function.

= 1.10 =

*March 6, 2021*

* Added text domain in plugin header information.

= 1.09 =

*March 6, 2021*

* Improved data sanitization and validation.
* Minor back-end improvements.
* Improved internationalization.

= 1.08 =

*October 20, 2020*

* Minor back-end improvements.

= 1.07 =

*October 10, 2020*

* Initial release.

== Screenshots ==
1. Dashboard menu with automatic links statistics
2. Configuration of a single automatic link
3. List of automatic links
4. Categories of automatic links
5. Term Groups menu
6. Options menu in the "Automatic Links" tab
7. Options menu in the "Link Analysis" tab
8. The "Autolinks Manager" sidebar in the post editor