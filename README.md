#Taylor

##UPDATES!##
####September 12, 2015####
Some major updates have been rolled in recently, including:
* Download WordPress (with the option of specifying a specific version)!
* Custom Theme is automatically activated
* Create DB User and DB if necessary
* Install and Activate WordPress!
* Automatically Download, Install and Activate Plugins!
* Plugins download from the Codex, provide an optional url param to install from anywhere
* Specify Plugin version (optional)
* Create and update wp-config.php with DB credentials and salts.

####Caveats####
The biggest pitfall to all of this is increased dependencies. To take advantage of all of these features,
you need the following software on your machine/server:
* wget
* zip
* tar
We're now also using a lot of unix based commands (as evident by the above list), so Windows will not be supported.

###New RoadMap###
Since a lot of things have at least been started, here is the updated roadmap. Note that a lot of this is "housekeeping" and opposed to developing cool new features.
* [ ] TESTING! A lot of new functionality has been added, and has not been thoroughly tested.
* [ ] Better exception and error handling. Right now most failures will throw exceptions, some may not need to throw exceptions but rather just inform the user (couldn't find a plugin, things like that).
* [ ] Refactoring! The code isn't a complete mess, yet, but it's on it's way.
* [ ] Automated content generation: when defining content types, let's create some sample content (optionally).
* [ ] Menu generation, should probably tie into the previous item somehow.
* [ ] Define assets (js/css) with location (header/footer) in manifest (built, needs more testing).
* [ ] While keeping the manifest file, let's drop the need to specify it when running the application, in other words, completely ditch the whole idea of passing different arguments to the application
* [ ] Better security, allow manifest to omit passwords and, instead, prompt the user for them.
* [ ] Create custom fields with ACF.
* [ ] Update the documentation instead of just putting this Updates section at the top and leaving the rest.

##ORIGINAL DOCS##

###Overview
Taylor is a bootstrapping tool for WordPress to help alleviate some of the pains and monotony of starting a new custom WordPress theme from scratch. Taylor takes a few command line arguments (or a manifest file) and streamlines processes such as creating a new theme with skeleton files (`functions.php`, `style.css`, headers and footers, etc.), creating custom post types, creating index pages for those post types, and creating templates for post types and index pages. There is a lot more planned in Taylor's immediate future, including custom fields support, but for now, in this pre-alpha phase, we're sticking to the basics.

###Quick Start
Just want to dig in an use it? Give the MANIFEST.md file a read and download the `taylor.phar` file from the `build` directory. Within your themes directory create a new folder, this is NOT the folder for your theme, this is a folder to put taylor in. Create a `taylor.manifest.json` file in the same directory. From the command line run `php taylor.phar manifest`.

###Usage
Drop Taylor into your themes directory (wp-content/themes). Follow the command line or manifest usage (below) by passing commands and arguments to the `src/taylor.php` script via the php cli.

###Required Plugins
Taylor doesn't require any WordPress plugins to run, though the theme is creates makes a few assumptions. The theme generated requires the Smarty For WordPress plugin. As development continues other plugins will become required, including Advanced Custom Fields. For now the required WordPress plugins are as follows.
* [Smarty for WordPress - version 3.1.21](https://wordpress.org/plugins/smarty-for-wordpress/)

###Command Line Usage

###Manifest Usage
Aside from regular command line usage, Taylor's real power lies in its manifest file system. Using the manifest file you can automate initial theme setup, asset inclusion (js & css), post type and taxonomy setup. The manifest file is a json file and must be named taylor.manifest.json. The manifest file must be in the same directory as the Taylor script. For technical requirements read the MANIFEST.md document and review the example taylor.manifest.json file included in the src directory.

###Road Map

* Advanced Custom Field integration
* Define assets (js/css) with location (header/footer) in manifest (built, needs more testing)
* Compress to Phar for portability (done, needs more testing)
* Install & activate plugins via manifest
* Set various configuration options via manifest
* Expand customization in manifest file, allow users to specify more options such as posts per page
* Though a default set of includes will be included in the Phar, allow users to specify their own includes directory in the manifest, this would allow users to use any template system they want and customize their boilerplate with things like custom default markup.

###Testing
Testers are always welcome and encouraged. Bugs, questions, and enhancements should be submitted as github issues. You can request "can you integrate with 'Plugin X'?" or "can you use 'Template Engine Y'?", but most likely those specific requests will not be addressed. Part of the road map is to open up the system to allow users to supply their own templates, and another part is to include the downloading and activation of plugins. These future enhancements should allow you to integrate with and plugins and utilize and templating system you choose.

###Open Source
Of course it's open source. The source code is found in the `src` directory and the main application file is `taylor.php`. You can modify `taylor.php` and then recompile a phar to the `build` directory simply by running `php borland.php` from the `src` directory.
