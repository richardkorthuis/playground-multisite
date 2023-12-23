=== Playground Multisite ===
Contributors: rockfire
Tags: playground, multisite
Requires at least: 6.0
Tested up to: 6.4
Requires PHP: 7.0
Stable tag: 0.1.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html

Convert a default WordPress Playground site to a multisite.

== Description ==

= THIS IS A BETA RELEASE, IT IS NOT WORKING YET! =

Upon activation of this plugin, the default WordPress Playground site will be converted to a multisite. The default site will be the main site of the multisite installation.

N.B. You will need to set the constant `WP_ALLOW_MULTISITE` to true before you activate this plugin.

== Installation ==

=== Using a blueprint ===

Make sure the following steps are added to your blueprint.json file:

`
"steps": [
    {
      "step": "defineWpConfigConsts",
      "consts": {
        "WP_ALLOW_MULTISITE": true
      }
    },
    {
      "step": "installPlugin",
      "pluginZipFile": {
        "resource": "url",
        "url": "https://github.com/richardkorthuis/playground-multisite/archive/refs/tags/0.1.0.zip"
      },
      "options": {
        "activate": true
      }
    }
]
`

== Frequently Asked Questions ==

= How do I set the `WP_ALLOW_MULTISITE` constant? =

Add the following step to your blueprint.json file:

`
{
  "step": "defineWpConfigConsts",
  "consts": {
    "WP_ALLOW_MULTISITE": true
  }
}
`

= Will I have a working multisite after activating this plugin? =

That is what we are trying to achieve, but it is not working 100% yet. At this point an error is thrown after the plugin has added the necessary multisite constants.

= Can the plugin add the right constants to wp-config.php? =

Yes, it can add the necessary constants to wp-config.php. You need to set the constant `PGMS_CONFIG_UPDATE` to true before you activate the plugin. But keep in mind: doing so will at this point make the playground show an error and become unworkable. We are working on finding a solutions that works.

== Changelog ==

= 0.1.0 =
Release Date: December 23rd, 2023

First beta release, not working 100% yet.