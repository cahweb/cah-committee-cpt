<?php
/**
 * CAH Committees Custom Post Type
 *
 * Custom Post Type class that defines the fields and properties of the CAH Committee CPT.
 *
 * PHP Version 7
 *
 * @category UCF\CAH
 * @package  WordPress\CPT
 * @author   Mike W. Leavitt <michael.leavitt@ucf.edu>
 * @license  GNU General Public License, v3.0 <https://opensource.org/licenses/GPL-3>
 * @version  SVN: 1.0.0
 * @link     https://cah.ucf.edu/about/administration/committees
 */
declare(strict_types = 1);

namespace UCF\CAH\WordPress\CPT;

use UCF\CAH\Lib\WordPress\WPCustomPostType;
use UCF\CAH\Lib\WordPress\WPCustomFieldsInterface;

final class Committee extends WPCustomPostType implements WPCustomFieldsInterface
{
    // Override necessary static member variables
    protected static $postTypeSlug = "cah-committee";
    protected static $labels = [
        'singular'    => 'Committee',
        'plural'      => 'Committees',
        'text_domain' => 'cah-committees'
    ];
    protected static $postTypeDesc = "A custom post type to represent committees in CAH.";
    protected static $menuPosition = 35;
    protected static $menuIcon = 'dashicons-groups';

    // Override
    protected static function getPostTypeSupports(): array
    {
        return [
            'title',
            'excerpt',
            'custom_fields'
        ];
    }

    /**
     * Sets up the necessary JavaScript in the admin screens, to manage
     * committee members.
     *
     * @param string $hookSuffix The "hook suffix," or the piece of the slug
     *                           that defines which admin page you're on.
     *
     * @return void
     */
    public static function enqueueAdminScripts(string $hookSuffix)
    {
        // If we're on one of these pages, we're editing (or creating) a post
        if (in_array($hookSuffix, ['post.php', 'post-new.php'])) {
            $screen = get_current_screen();
            
            // If it's this post type, we load our scripts
            if (is_object($screen) && $screen->post_type == static::$postTypeSlug) {
                $uri  = COMMITTEES__PLUGIN_URI;
                $path = COMMITTEES__PLUGIN_DIR;

                wp_enqueue_script(
                    'cah-committee-admin-script',
                    "${uri}js/cah-committees-admin.min.js",
                    [],
                    filemtime("${path}js/cah-committees-admin.js"),
                    true
                );

                wp_enqueue_style(
                    'cah-committee-admin-style',
                    "${uri}css/cah-committees-admin.min.css",
                    [],
                    filemtime("${path}css/cah-committees-admin.css"),
                    'all'
                );
            }
        }
    }

    /**
     * Enqueues the front-end scripts for switching committees in the
     * page-committees.php template.
     *
     * @return void
     */
    public static function enqueueCommitteesScript()
    {
        // We only want to keep going if we have a $post object
        global $post;
        if (!isset($post) || !is_object($post)) {
            return;
        }

        $uri  = COMMITTEES__PLUGIN_URI;
        $path = COMMITTEES__PLUGIN_DIR;

        // If we're on the committees page, load our script
        if (!is_archive() && $post->post_type == 'page' && 'committees' == $post->post_name) {
            wp_enqueue_script(
                'cah-committee-script',
                "${uri}js/cah-committees.min.js",
                [],
                filemtime("${path}js/cah-committees.min.js"),
                true
            );
        }
    }

    /**
     * Makes sure we have a template for our committees page. Doing it this way
     * means that we can include the template in the plugin, rather than having
     * to copy/paste it into every theme where we want to use it.
     *
     * @param string $template The path to the page template WordPress is planning
     *                         to use.
     *
     * @return void
     */
    public static function pageTemplate(string $template)
    {
        // If we don't have a $post object or this isn't our committees page, 
        // don't change anything
        global $post;
        if (!isset($post) || !is_object($post) || $post->post_name !== 'committees') {
            return $template;
        }

        /*
         * If WordPress hasn't already selected a template and this isn't an archive
         * page (i.e., it should be handled with a theme-level archive template via
         * archive-cah-committees.php), then we check things in this order:
         *
         * 1. See if the current theme has a template defined
         * 2. See if the parent theme (if applicable) has a template defined
         * 3. If neither of those things are true, use the template supplied in the
         *    plugin.
         */
        if (stripos($template, 'page-committees.php') === false && !is_archive()) {
            if (file_exists(get_stylesheet_directory() . "/page-committees.php")) {
                $template =  get_stylesheet_directory() . "/page-committees.php";
            } elseif (file_exists(get_template_directory() . "/page-committees.php")) {
                $template = get_template_directory() . "/page-committees.php";
            } else {
                $template = COMMITTEES__PLUGIN_DIR . "includes/templates/page-committees.php";
            }
        }

        // Return whatever template we've come up with
        return $template;
    }

    /**
     * Sets up our custom fields by adding the appropriate actions. Required by
     * WPCustomFieldsInterface.
     *
     * @return void
     */
    public static function setupCustomFields()
    {
        $type = static::$postTypeSlug;
        add_action("save_post_$type", [__CLASS__, "savePost"], 10, 0);
        add_action('add_meta_boxes', [__CLASS__, "addMetaBox"], 10, 0);
    }

    /**
     * Handles saving the extra metadata we add to our custom post type--namely,
     * our committee members. Required by WPCustomFieldsInterface.
     *
     * @return void
     */
    public static function savePost()
    {
        // If we're not actually trying to update a post, leave
        // (This prevents an error when creating a new post.)
        if (!isset($_POST['post_ID'])) {
            return;
        }

        // Checks to see if we have values in these fields, or initializes them to an
        // empty array
        $names  = isset($_POST['member-name']) && !empty($_POST['member-name'])
            ? $_POST['member-name']  : [];
        $depts  = isset($_POST['member-dept']) && !empty($_POST['member-dept'])
            ? $_POST['member-dept']  : [];
        $phones = isset($_POST['member-phone']) && !empty($_POST['member-phone'])
            ? $_POST['member-phone'] : [];
        $terms  = isset($_POST['member-term']) && !empty($_POST['member-term'])
            ? $_POST['member-term']  : [];

        // Initialize the list of committee members.
        $members = [];

        // If we have nothing else for committee members, we'll have their names,
        // so we'll use that to guide us in terms of how many we should grab.
        for ($i = 0; $i < count($names); $i++) {
            // Ignore empty entries
            if (empty($names[$i]) && empty($depts[$i]) && empty($phones[$i]) && empty($terms[$i])) {
                continue;
            }

            // Create a new array entry with the data in the right places
            $newMember = [
                'name'  => $names[$i],
                'dept'  => $depts[$i],
                'phone' => $phones[$i],
                'term'  => $terms[$i],
            ];

            // Add it to the list
            $members[] = $newMember;
        }

        // Update the post meta. Since it's an array, WordPress will automatically
        // serialize it (though it doesn't automatically *un*serialize it, which
        // doesn't make sense to me)
        update_post_meta($_POST['post_ID'], 'cah_committee_members', $members);
    }

    /**
     * Adds all the metaboxes we need--one, in this case. Required by
     * WPCustomFieldsInterface.
     *
     * @return void
     */
    public static function addMetaBox()
    {
        add_meta_box(
            'committee-members',
            "Committee Members",
            [__CLASS__, "buildMetaBox"]
        );
    }

    /**
     * Provides the HTML for our metabox. Should echo the output, rather
     * than returning it. Required by WPCustomFieldsInterface.
     *
     * @return void
     */
    public static function buildMetaBox()
    {
        global $post;

        // Retrieve the array of members from the post meta
        $members = maybe_unserialize(
            get_post_meta($post->ID, 'cah_committee_members', true)
        );

        // Initialize the array so we create at least one empty box to be filled in.
        if (empty($members)) {
            $members = [
                [
                    'name'  => '',
                    'dept'  => '',
                    'phone' => '',
                    'term'  => '',
                ],
            ];
        }

        // Add the members, and set up the various buttons to modify them.
        ?>
        <div class="wrap" id="members">
        <?php foreach ($members as $i => $member) : ?>
            <div class="member-box" id="member-<?= $i ?>">
                <div class="member-fields">
                <?php foreach ($member as $field => $value) : ?>
                    <div class="form-group">
                        <label for="member-<?= "$field-$i" ?>"><strong><?= ucfirst($field) ?>:</strong></label>
                        <input type="<?= $field == 'phone' ? 'tel' : 'text' ?>" id="member-<?= "$field-$i" ?>" name="member-<?= $field ?>[]" value="<?= $value ?>"<?= $field == 'term' ? ' maxlength="9"' : '' ?>>
                    </div>
                <?php endforeach; ?>
                </div>
                <div class="delete-button-container">
                    <button type="button" class="button-secondary delete" id="delete-<?= $i ?>">&minus;</button>
                </div>
            </div>
        <?php endforeach; ?>
            <div class="buttons">
                <button type="button" class="button-primary" id="addButton">&plus;</button>
            </div>
        </div>
        <?php
    }
}
