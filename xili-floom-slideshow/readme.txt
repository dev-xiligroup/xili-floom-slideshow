=== xili floom slideshow ===
Contributors: michelwppi, MS xiligroup
Donate link: http://dev.xiligroup.com/xili-floom-slideshow/
Tags: theme, floom, Post, plugin, posts, mootools, slideshow, shortcode, javascript, extended class, css, iPhone, iPod, iPad, gallery, child theme, post-thumbnails
Requires at least: 3.0
Tested up to: 3.9
Stable tag: 1.3.2
License: GPLv2

xili-floom-slideshow integrates the floom slideshow in WordPress theme or child theme.

== Description ==

*xili-floom-slideshow integrates the Floom slideshow in wordpress theme.*

Floom slideshow designed by [Oskar Krawczyk](http://nouincolor.com/) under MIT license is wonderful and amazing. For integration inside wordpress, it can be awesome ! xili-floom-slideshow tries to install it automatically but also allows personalizations.
= How it works ? =
**xili-floom-slideshow** inserts the javascript and css file inside the header of the theme. The images attached (but not inserted) to a post (or a page) are listed for the slideshow. And after adding a `[xilifloom]` shortcode inside the content of the post, the slideshow of the images of the gallery are automatically displayed.
With the dashboard Settings page, it is possible to change some properties of the slideshow without changing the original javascript: *by example, number of vertical 'venitian' blinds, speed, progress bar, visible captions, and [more](http://blog.olicio.us/2009/07/25/floom/).*
Some properties can be attached to one post by using custom fields and to one shortcode by using params.

= Version 1.3.2 =
* Last Updated 2021-03-25
* RW according WPCS - Mootools v1.6.0
*
* Last Updated 2014-04-18 
* See [tab and chapters in changelog](http://wordpress.org/extend/plugins/xili-floom-slideshow/changelog/)
= roadmap =
* style according image_size,
* default settings screen.
* try to find a clone based on jQuery !

= from 0.9.x to 1.2 =
* by default display full size of attached images. But can also display other sizes as define by default (large, medium, thumbnail) or  those set with `add_image_size( 'my-size-example', 600, 210 );` function. Use 'floom_image_size' in custom post field or 'image_size' param in Shortcode.
* example of shortcode `[xilifloom image_size="my-size-example"]`. Be aware that current css is adapted !
* New [xili wiki](http://wiki.xiligroup.org)
* enable now to have more than one slideshow displayed one resulting webpage. **Need a minimum of knowledges** in WP (shortcode), CSS, JS to activate these "flooms" and avoid bad side effects when more than one slideshow.
* BE AWARE : now xili-floom-slideshow needs that theme have both `wp_head()` (as before)  **AND**  `wp_foot()` template tags in header and footer as in default theme like twentyten or twentyeleven or the most current well designed.
* Improved filter `xili_floom_get_values` has now 2 params : developers must read source.
* multiple flooms example [here](http://2011.wpmu.xilione.com/xili-floom-slideshow-demo/ "xili-floom-slideshow demo") !
* OOP new source code
* new *like* function because changes in WP 3.0 when naming file and slug of attachment images. Possible to choose post column (`post_name or guid or…`) to sub-select with `floom_subname` postmeta
* developers using global `$xilifloom_name_selector` must change to function `set_xilifloom_name_selector()` - see code at end of source
* compatibility with child theme as visible in new theme of [dev.xiligroup.com](http://dev.xiligroup.com/) - child example of default twentyten -
* add thumbnail bar with shortcode [xilifloombar]
* Gold parameters added : ready to integrate a new child class of Floom [see this post](http://dev.xiligroup.com/?p=1357). Open to better events exchanged with theme UI. More modularity and possibility of setting. (*Gold options are reserved for theme designer and webmaster with sufficient knowledge in php, js,...*)
* CAUTION: after upgrading, if `floom_subname` is used in custom fields of some posts, to retrieve the images series, the wildcard must be wrapped with one or two chars '%' as in **LIKE** of sql query.
* add for pictures order by menu_order. If order is set in gallery linked to a post, displayed series is ordered by these numbers ascendant.
* more parameters.
* add hooks and filters : to allow better selection of floom values and choice of series of images (not necessary attached to a post) according the theme or cms architecture, two filters was added : `xili_floom_get_values` and `xili_floom_get_images` insertable in functions inside `functions.php` of the current theme. Very useful to personalize header according place inside the site architecture. [example](http://dev.xiligroup.com/?cat=529&lang=en_us) 

= prerequisite =

* Minimun knowledges in Wordpress architecture (and css).
* If others plugins use mootools framework, some modifications must be done through a added function named `xilifloom_theme_header()` inside functions.php - see source. 
* Images for a slideshow must be selected with great precaution. (Same size adapted to the frame)


== Installation ==
1. Upload the folder containing `xili-floom-slideshow.php` and other sub-folders and files to the `/wp-content/plugins/` directory.
2. Activate the plugin.
3. If you want to personalize the look and size of the frame of the slideshow, don't modify the default css and files present inside plugin's folder. Create a floom folder (and a css sub-folder) inside your current theme. Inside this, copy the floom.css, spiner.gif and frame bg image that you can adapt. xili-floom-slideshow plugin will detect automatically this folder.

= Personalizations =

Some **Custom fields** are possible: `floom_divs` to set id of the div containing images ; `floom_captions` (true or false) to display or not the caption (title) of the images ; `floom_progressbar`  (true or false) to show (or not) the progress bar. `floom_title_desc` is set by default to 1 and display the title of the attached images. (2 : only the description and 3 : both title and description).

* `floom_parentID` to choose attachment from another post (and not the current post where slideshow in inserted)
* `floom_subname` to sub-select a series of pictures attached by set a mask (use % as LIKE in SQL %img or %pict%...) [See post](http://dev.xiligroup.com/?p=1269).
* `floom_container` to inform name of container (default : blinds-cont).
* `floom_display` to decide if the container is displayed when no image (block or none) - default : block.

The following custom fields (prefix floom_) work like javascript parameters. Use them with caution..

* `floom_amount` (number of blinds)
* `floom_interval` (interval between change)
* `floom_axis` (by default : vertical)

It is also possible to fireEvent (onSlideChange and onPreload) by choosing name of fired functions (javascript added by functions in current theme).

**Example of parameters in shortcode:**

`[xilifloom frame_id="mondrian-top-left" blinds_id="mblinds" captions="false" children="211" amount="20" ]`

multiple flooms example [here](http://2011.wpmu.xilione.com/xili-floom-slideshow-demo/ "xili-floom-slideshow demo") !

**Gold parameters**
If active in plugin settings, a wide range of features are open for special js effects on first or last slide and on other events of the child class... [see this post](http://dev.xiligroup.com/?p=1357)

= xilitheme-select plugin compatibility (for iPhone) =
As in this website [dev.xiligroup.com](http://dev.xiligroup.com/), it is now possible to specify a floom.css in each theme (the for desktop, the for mobile as iPhone or iPod).



== Frequently Asked Questions ==

= Is it possible to insert slideshow outside the content of post on CMS ? =

Yes, xili-floom-slideshow only need to find the id of the div where images are displayed (default name **blinds**).

= With latest version 0.9.8 and possibility to have more than one floom in resulting webpage, what about the settings ? =
* In preliminary, it is very important to understand how xili-floom-slideshow works to avoid bad side effects. The insertion of specific js (domready) is done when `wp_footer()` is called so after counting the floom shortcode.
For the settings, if not in shortcode, try to find in postmeta of displayed post and finally keep those in plugin's setting. 

* Another important thing is to prepare the style.css of the theme or the floom.css  in subfolder floom/css inserted in the theme. (don't modify the css in plugin, use it as example.


= What happen when iPhone or iPod visit the website ? =
As you know, flash is not compatible with iPhone, but javascript and Floom is !
If xilitheme-select plugin is activated, the theme for iPhone is selected and the floom.css inside this theme is choosen. [see snapshot](http://wordpress.org/extend/plugins/xili-floom-slideshow/screenshots/).

= Is xili-floom-slideshow plugin compatible with other plugins based on Mootools ? = 

Yes, but be aware to add a special function in your functions.php. See example [here](http://dev.xiligroup.com/xili-floom-slideshow/).

= Is is possible to display progressive texts ? =

Yes, by creating a line by line image series like [here](http://www.presse-infosplus.fr/).

= Is is possible to display more than one floom in a webpage or a singular post ? =

Yes, but don't forget that the theme css must contains all div styles for each slideshow (with unique id).

= What happens with default divs (frame and blinds) when more than one ? =

xili-floom-slideshow plugin creates unique ids based on the default one : blinds-cont, blinds-cont-2,… and blinds, blinds-2,… (to be also compatible with previous versions).

= Support Forum or contact form ? =

Effectively, prefer [forum](http://forum2.dev.xiligroup.com/) to have support (with delay around one or two days - here European Time).

== Screenshots ==

1. Settings page.
2. Folder example in current theme.
3. Blinds during transition between two images (snapshot from Oskar example).
4. Example in iPhone Safari.
5. Ordered list of images (only these in slideshow are affected).

== Changelog ==

= 1.3 (2014-04-18) =
* few improvements for WP 3.9

= 1.2 =
* fixes and notice for WP3.5.1
= 1.1 (2012-07-22) =
* setting to choose images size (default full or as (large, medium, thumbnail) and those defined by add_image_size(); )
= 0.9.8, 0.9.9 =
* now possible to have more than on floom (via shortcode) on a webpage. Improved and more params in shortcode `[xilifloom]`.

= 0.9.6, 0.9.7 = 
* OOP source code
* new *like* function because changes in WP 3.0 when naming file and slug of attachment images. Possible to choose post column (`post_name or guid or…`) to sub-select by `floom_subname`
* developers using global `$xilifloom_name_selector` must change to function `set_xilifloom_name_selector()` - see code at end of source

= 0.9.5 = 
* integrate child theme better
= 0.9.4 = 
* add thumbnail bar with Shortcode [xilifloombar]
= 0.9.3 =

* update query with subname as LIKE in sql (need now % char in custom fields).
* add Gold functions and ability to use child class of floom.
* some fixes

= 0.9.2 =

* add for pictures orderby menu_order 
* add hooks and filter
* add container and display params
* fixes (quoted captions)

= 0.9.1 =

* xilitheme-select plugin compatibility (for iPhone), 
* add options to fireEvents, 
* fixes,...

= 0.9.0 =

* first public release

© 2014-04-18 MS dev.xiligroup.com

== Upgrade Notice ==

* Plugin only use Options table in WP database.
* if update via desktop ftp : erase previous version folder before uploading latest version.
* As usual before upgrading, read carefully the readme.txt and backup your database.
* Read code source if you use elsewhere mootools library.


