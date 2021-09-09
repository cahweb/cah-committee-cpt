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

    public static function enqueueAdminScripts(string $hookSuffix)
    {
        if (in_array($hookSuffix, ['post.php', 'post-new.php'])) {
            $screen = get_current_screen();
            
            if (is_object($screen) && $screen->post_type == static::$postTypeSlug) {
                $uri  = COMMITTEES__PLUGIN_URI;
                $path = COMMITTEES__PLUGIN_DIR;

                wp_enqueue_script(
                    'cah-committee-admin-script',
                    "${uri}js/cah-committees-admin.js",
                    [],
                    filemtime("${path}js/cah-committees-admin.js"),
                    true
                );

                wp_enqueue_style(
                    'cah-committee-admin-style',
                    "${uri}css/cah-committees-admin.css",
                    [],
                    filemtime("${path}css/cah-committees-admin.css"),
                    'all'
                );
            }
        }
    }

    public static function enqueueCommitteesScript()
    {
        global $post;

        $uri  = COMMITTEES__PLUGIN_URI;
        $path = COMMITTEES__PLUGIN_DIR;

        if (!is_archive() && $post->post_type == 'page' && 'committees' == $post->post_name) {
            wp_enqueue_script(
                'cah-committee-script',
                "${uri}js/cah-committees.js",
                [],
                filemtime("${path}js/cah-committees.js"),
                true
            );
        }
    }

    public static function pageTemplate(string $template)
    {
        global $post;
        if (!isset($post) || !is_object($post) || $post->post_name !== 'committees') {
            return $template;
        }

        if (stripos($template, 'page-committees.php') === false && !is_archive()) {
            // Check the current theme for a template, just in case, then check the
            // parent theme. If we don't find anything, enqueue our template.
            if (file_exists(get_stylesheet_directory() . "/page-committees.php")) {
                $template =  get_stylesheet_directory() . "/page-committees.php";
            } elseif (file_exists(get_template_directory() . "/page-committees.php")) {
                $template = get_template_directory() . "/page-committees.php";
            } else {
                $template = COMMITTEES__PLUGIN_DIR . "includes/templates/page-committees.php";
            }
        }

        return $template;
    }

    public static function setupCustomFields()
    {
        $type = static::$postTypeSlug;
        add_action("save_post_$type", [__CLASS__, "savePost"], 10, 0);
        add_action('add_meta_boxes', [__CLASS__, "addMetaBox"], 10, 0);
    }

    public static function savePost()
    {
        if (!isset($_POST['post_ID'])) {
            return;
        }

        $names  = isset($_POST['member-name']) && !empty($_POST['member-name']) ? $_POST['member-name']  : [];
        $depts  = isset($_POST['member-dept']) && !empty($_POST['member-dept']) ? $_POST['member-dept']  : [];
        $phones = isset($_POST['member-phone']) && !empty($_POST['member-phone']) ? $_POST['member-phone'] : [];
        $terms  = isset($_POST['member-term']) && !empty($_POST['member-term']) ? $_POST['member-term']  : [];

        $members = [];
        for ($i = 0; $i < count($names); $i++) {
            if (empty($names[$i]) && empty($depts[$i]) && empty($phones[$i]) && empty($terms[$i])) {
                continue;
            }
            $newMember = [
                'name'  => $names[$i],
                'dept'  => $depts[$i],
                'phone' => $phones[$i],
                'term'  => $terms[$i],
            ];

            $members[] = $newMember;
        }

        update_post_meta($_POST['post_ID'], 'cah_committee_members', $members);
    }

    public static function addMetaBox()
    {
        add_meta_box(
            'committee-members',
            "Committee Members",
            [__CLASS__, "buildMetaBox"]
        );
    }

    public static function buildMetaBox()
    {
        global $post;

        // Retrieve the array of members from the post meta
        $members = maybe_unserialize(get_post_meta($post->ID, 'cah_committee_members', true));

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
