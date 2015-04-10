#Taylor Manifest Anatomy
This document outlines the structure and requirements of the taylor.manifest.json file. This document should be reviewed with the sample taylor.manifest.json file included in the src directory.

* root - the manifest file contains one root json object.
    * `commands` - required, an array of json objects.
        * command objects - The objects each have a single key (the Taylor function to run) and a value (a json object of arguments to pass to the Taylor function).
            * `init` 
                * `theme_dir` - string, the name of the directory to create for the theme, vaule must be a legal name for a directory on the given file system
                * `project_name` - string, the name of the theme as it will appear in the theme selection menu
                * `project_uri` - string, the uri(url) for the theme
                * `author_name` - string, the name of the author
                * `author_uri` - string, the uri(url) for the author
                * `javascripts` - array of objects, javascript files to be included, in the functions.php file for the theme
                    * `path` - string, a url or relative path to a js file. If a path is provided it should be relative, but what it's relative to does not matter so long as it lives either within the same directory as the Taylor script or within an parent directory. If a url is specified and being copied (see below) a protocol must be specified, protocol-agnostic uris (`//`) will not work
                    * `asset_path` - string, a path to nest the copied asset in, relative to the theme root
                    * `drop_root` - boolean (integer), whether or not to drop the top most directory when copying the asset, default: false
                    * `footer` - boolean (integer), whether or not to include the asset in the footer
                    * `copy` - boolean (integer), whether or not to copy the source asset to the local theme. Local/relative files are always copied regardless of this setting, default: false
                * `styles` - array of objects, css files to be included, in the functions.php file for the theme
                    * `path` - string, a url or relative path to a js file. If a path is provided it should be relative, but what it's relative to does not matter so long as it lives either within the same directory as the Taylor script or within an parent directory. If a url is specified and being copied (see below) a protocol must be specified, protocol-agnostic uris (`//`) will not work
                    * `asset_path` - string, a path to nest the copied asset in, relative to the theme root
                    * `drop_root` - boolean (integer), whether or not to drop the top most directory when copying the asset, default: false
                    * `media` - string, optional, the specific media the asset applies to
                    * `copy` - boolean (integer), whether or not to copy the source asset to the local theme. Local/relative files are always copied regardless of this setting, default: false
                * `menus` - array of objects, nav menus to be registered and included in the global smarty object
                    * menu object - has a single key (the "location" for the menu -- also used as the smarty variable name) and an optional value that is a single object containing optional parameters
                        * `container` - string, the `container` argument when registering a nav menu, the node name of an html element used to wrap the entire menu
                        * `class` - string, the `class` argument when registering a nav menu, a css class
                        * `name` - string, the name of the menu as displayed in the admin area, if not provided a name will be generated based on the menu objects "key" (the "location")
            * `create_post_type` - create a custom post type with templates for both a single and index view. Takes a json object of arguments.
                * `type` - string, the name of the post type
                * `plural` - string, the plural name of the post type
                * `singular` - string, the singular name of the post type
                * `slug` - string, the url rewrite slug of the post type
                * `templates` - boolean (integer), whether or not to generate templates, default: false
            * `create_taxonomy` - creates a custom taxonomy attached to a post type (standard or custom). Takes a json object of arguments.
                * `taxonomy` - string, the name of the taxonomy
                * `type` - string, the post type to attach it to
                * `plural` - string, the plural name of the taxonomy
                * `singular` - string, the singular name of the taxonomy
                * `hierarchical` - boolean (integer), whether or not the taxonomy is hierarchical