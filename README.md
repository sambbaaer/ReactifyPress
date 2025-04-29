=== ReactifyPress ===
Contributors: yourname
Tags: reactions, emoticons, engagement, feedback, social
Requires at least: 5.6
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allow visitors to react to your posts with different emoticons, similar to Facebook or LinkedIn reactions.

== Description ==

ReactifyPress is a lightweight yet powerful WordPress plugin that allows your visitors to react to your content with various emoticons, similar to the reaction functionality on popular social networks like Facebook and LinkedIn.

Increase engagement on your website by giving your readers an easy and fun way to express their feelings about your content without having to write a comment.

### Key Features

* **Multiple Reaction Types**: By default, the plugin includes 6 reactions (Like, Love, Haha, Wow, Sad, Angry), but you can add, remove, or customize them.
* **Hover Effects**: When users hover over a reaction, a tooltip appears showing the name/meaning of the reaction.
* **Customizable Backend**: Admin can change emoticons (icons or emojis) and reaction names through an intuitive settings page.
* **Color Customization**: Easily change colors (background, hover, active, text) directly from the backend.
* **Flexible Display Options**: Choose where to show reactions (before content, after content, both, or manual placement via shortcode).
* **Modular Structure**: Code is organized into separate modules/classes for easy maintenance and extensibility.
* **Performance Optimized**: Scripts and styles are loaded only when needed and only on pages where reactions are enabled.
* **Developer Friendly**: Built following WordPress coding standards with a focus on extensibility.
* **Elementor Compatible**: Includes an Elementor widget for easy integration with Elementor page builder.
* **Analytics Dashboard**: Track and analyze reaction data through a dedicated dashboard.

### Usage

After activation, reactions will automatically appear after your post content (default setting). You can change this in the plugin settings.

To manually place reactions on your site, use the shortcode:

`[reactifypress]`

Or with a specific post ID:

`[reactifypress post_id="123"]`

### Elementor Integration

ReactifyPress includes an Elementor widget, making it easy to add reactions to your Elementor-built pages.

### Developers

ReactifyPress is built with developers in mind. The code is well-organized, properly documented, and follows WordPress coding standards. You can extend or customize the plugin to fit your specific needs.

== Installation ==

1. Upload the `reactifypress` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'ReactifyPress' in your WordPress admin menu to configure the plugin

== Frequently Asked Questions ==

= Can I customize the reaction icons? =

Yes, you can use any emoji or icon for reactions through the plugin settings.

= Will reactions work with my theme? =

ReactifyPress is designed to work with most WordPress themes. The styles are minimal and focused on the reaction elements themselves.

= Can I disable reactions on certain post types? =

Yes, you can choose which post types should display reactions in the plugin settings.

= Do visitors need to be logged in to use reactions? =

No, both logged-in users and guests can react to your content. For guests, reactions are tracked using IP addresses.

= Is the plugin compatible with Elementor? =

Yes, ReactifyPress includes an Elementor widget for easy integration.

= Can I display reactions in different locations? =

Yes, you can display reactions before content, after content, or both. You can also manually place them anywhere using a shortcode or the Elementor widget.

== Screenshots ==

1. Frontend display of reactions
2. Admin settings - Manage reactions
3. Admin settings - Appearance
4. Admin settings - Display options
5. Analytics dashboard

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release of ReactifyPress

== Additional Info ==

**Plugin Structure:**

* `includes/` - Plugin core classes
  * `class-reactifypress-db.php` - Database handling
  * `class-reactifypress-settings.php` - Settings management
  * `class-reactifypress-frontend.php` - Frontend display
  * `class-reactifypress-admin.php` - Admin interface
  * `class-reactifypress-ajax-handler.php` - AJAX functionality
  * `class-reactifypress-shortcode.php` - Shortcode functionality
  * `elementor/` - Elementor integration
* `assets/` - JavaScript and CSS files
  * `css/` - Stylesheets
  * `js/` - JavaScript files
* `templates/` - Admin page templates
* `languages/` - Translation files

**Database Structure:**

The plugin creates one database table:

* `wp_reactifypress_reactions` - Stores reaction data (post_id, user_id, user_ip, reaction_type, date_created)
