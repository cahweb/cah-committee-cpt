<?php
/**
 * Template Name: CAH Committees Page Template
 */

$committees = [];
$excerpts = [];
$memberLists = [];

$queryArgs = [
    'post_type' => 'cah-committee',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'ID',
    'order' => 'ASC',
];

$loop = new WP_Query($queryArgs);

// We're going to do the Loop early, to just grab all the data really quickly.
if ($loop->have_posts()) {
    while ($loop->have_posts()) {
        $loop->the_post();
        $committees[] = get_the_title();
        $excerpts[] = get_the_excerpt();
        $id = get_the_ID();
        $members = maybe_unserialize(get_post_meta($id, 'cah_committee_members', true));
        $memberLists[] = $members;
    }
}
wp_reset_postdata();

get_header();
?>
<div class="container mt-4 mb-5">
    <p>Select a committee to see a list of its members.</p>
    <div class="row">
        <div class="form-group col-md-6 col-lg-4">
            <select id="committee-select" class="form-control mb-3">
            <?php foreach ($committees as $i => $name) : ?>
                <option value="<?= $i ?>"><?= $name ?></option>
            <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
        <?php foreach ($excerpts as $i => $excerpt) : ?>
            <p id="excerpt-<?= $i ?>" class="excerpt pl-2<?= $i > 0 ? " d-none" : ""?>"><?= $excerpt ?></p>
        <?php endforeach; ?>
        </div>
    </div>
    <div class="row">
        <div id="committee-display" class="col-12">
            <table class="table table-striped table-hover">
                <thead class="thead-inverse">
                    <th>Department</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Term</th>
                </thead>
            <?php foreach ($memberLists as $i => $list) : ?>
                <tbody class="committee-list<?= $i > 0 ? " d-none" : ""?>" id="committee-<?= $i ?>" data-active="<?= $i == 0 ? 1 : 0?>">
                <?php if (!empty($list)) : ?>
                    <?php foreach ($list as $member) : ?>
                        <tr>
                            <td><?= $member['dept'] ?></td>
                            <td><?= $member['name'] ?></td>
                            <td><?= $member['phone'] ?></td>
                            <td><?= $member['term'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="4" class="text-center"><strong>No members available to display for this committee.</strong></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>
<?php
get_footer();
