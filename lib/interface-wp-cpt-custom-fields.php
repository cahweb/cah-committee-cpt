<?php
/**
 * WordPress Custom Post Type Custom Fields Interface
 *
 * Interface outlining the necessary methods to implement custom field
 * metaboxes in the WordPress Classic Editor.
 *
 * PHP Version 7
 *
 * @category UCF\CAH
 * @package  Lib\WordPress
 * @author   Mike W. Leavitt <michael.leavitt@ucf.edu>
 * @version  SVN: 1.0.0
 * @license  GNU General Public License, v3.0 (https://opensource.org/licenses/GPL-3.0)
 * @link     https://cah.ucf.edu/
 */
declare(strict_types = 1);

namespace UCF\CAH\Lib\WordPress;

interface WPCustomFieldsInterface
{
    /**
     * Function to link the various functions to the appropriate WordPress hooks, so
     * the custom fields can function properly.
     *
     * @return void
     */
    public static function setupCustomFields();

    /**
     * Function to be called to save custom metabox data. Attached to the
     * "save_post_${postTypeSlug}" action hook.
     *
     * @return void
     */
    public static function savePost();

    /**
     * Function to add one or more metaboxes, along with their callback functions.
     * Attached to the 'add_meta_boxes' action hook. Each metabox should have a
     * unique callback to build its HTML output.
     *
     * @return void
     */
    public static function addMetaBox();

    /**
     * Callback function to provide HTML output for the metabox, usually in the style
     * of a form (though you don't have to provide the form tags themselves). Function
     * should echo its output, rather than returning it. There should be one unique
     * callback function for each metabox; this Interface provides this first one by
     * way of example.
     *
     * @return void
     */
    public static function buildMetaBox();
}
