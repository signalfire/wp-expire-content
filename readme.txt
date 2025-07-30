=== Signalfire Expire Content ===
Contributors: signalfire
Tags: expiration, content, posts, pages, scheduling
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Adds expiration functionality to posts and pages with customizable actions when content expires.

== Description ==

Signalfire Expire Content allows you to set expiration dates and times for your WordPress posts and pages. When content expires, you can choose to automatically change it to draft status or redirect visitors to a custom URL.

**Key Features:**

* Set expiration date and time for posts and pages
* Choose expiration action: change to draft or redirect to URL
* Easy-to-use metabox on post/page edit screens
* Admin column showing expiration status
* Automatic expiration checking on frontend
* Security-focused with proper nonces and sanitization
* Multilingual ready with translation support

**Perfect for:**

* Time-sensitive content like promotions or events
* Temporary pages that need automatic removal
* Content that should redirect after a certain date
* Managing seasonal or limited-time offers

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/signalfire-expire-content` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. When editing posts or pages, you'll see an "Expiration Settings" metabox in the sidebar.
4. Set your desired expiration date, time, and action.

== Frequently Asked Questions ==

= What happens when content expires? =

You can choose between two actions:
* **Change to Draft**: The post/page will automatically be changed to draft status and visitors will be redirected to your homepage.
* **Redirect to URL**: Visitors will be automatically redirected to a URL you specify.

= Can I see which posts are set to expire? =

Yes! The plugin adds an "Expiration" column to your posts and pages admin screens showing the expiration date and action for each item.

= What happens if I republish an expired post? =

When you change an expired post back to "Published" status, the plugin automatically clears all expiration data to prevent it from immediately expiring again.

= Does this work with custom post types? =

Currently, the plugin supports posts and pages. Custom post type support may be added in future versions.

= Is the plugin translation ready? =

Yes! The plugin includes proper internationalization and is ready for translation. Translation files should be placed in the `/languages` directory.

== Screenshots ==

1. Expiration Settings metabox on post edit screen
2. Admin column showing expiration status
3. Date and time picker interface

== Changelog ==

= 1.0.0 =
* Initial release
* Expiration functionality for posts and pages
* Choice between draft and redirect actions
* Admin column integration
* Security and sanitization features
* Translation ready

== Upgrade Notice ==

= 1.0.0 =
Initial release of Signalfire Expire Content plugin.