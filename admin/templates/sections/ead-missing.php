<?php

if (!defined('ABSPATH')) exit;

// Verify we have the required data
if (!isset($selected_post_type) || !isset($exi_post_type_meta_url)) {
    return;
}

?>

<div id="exi-missing" class="exi-section">
    <div class="exi-missing-section">
        <h2 class="exi-toggle">
            <span class="exi-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-unlink">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M17 22v-2" />
                    <path d="M9 15l6 -6" />
                    <path d="M11 6l.463 -.536a5 5 0 0 1 7.071 7.072l-.534 .464" />
                    <path d="M13 18l-.397 .534a5.068 5.068 0 0 1 -7.127 0a4.972 4.972 0 0 1 0 -7.071l.524 -.463" />
                    <path d="M20 17h2" />
                    <path d="M2 7h2" />
                    <path d="M7 2v2" />
                </svg>
                <?= __('Missing EAD URL', 'ead-xml-importer'); ?> <span class="desc"> - <?= __('List all archive without EAD URL', 'ead-xml-importer') ?>.</span>

            </span>
            <span class="exi-arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-chevron-down">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M6 9l6 6l6 -6" />
                </svg>
            </span>
        </h2>
        <div class="exi-content exi-content--open">
            <!-- Get all items of the cpt $post_types, look the meta fields $exi_post_type_meta_url -->
            <div id="exi-missing-container" style="display: block;">
                <?php
                global $wpdb;

                // Get current page number
                $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
                $items_per_page = 10;
                $offset = ($current_page - 1) * $items_per_page;

                // Count total items
                $count_query = $wpdb->prepare(
                    "SELECT COUNT(DISTINCT p.ID) 
        FROM {$wpdb->posts} p 
        LEFT JOIN {$wpdb->postmeta} pm ON (p.ID = pm.post_id AND pm.meta_key = %s)
        WHERE p.post_type = %s 
        AND p.post_status = 'publish'
        AND (pm.meta_value IS NULL OR pm.meta_value = '')",
                    $exi_post_type_meta_url,
                    $selected_post_type
                );

                $total_items = $wpdb->get_var($count_query);
                $total_pages = ceil($total_items / $items_per_page);

                // Get paginated results
                $query = $wpdb->prepare(
                    "SELECT p.ID, p.post_title, p.post_date 
        FROM {$wpdb->posts} p 
        LEFT JOIN {$wpdb->postmeta} pm ON (p.ID = pm.post_id AND pm.meta_key = %s)
        WHERE p.post_type = %s 
        AND p.post_status = 'publish'
        AND (pm.meta_value IS NULL OR pm.meta_value = '')
        ORDER BY p.post_date DESC
        LIMIT %d OFFSET %d",
                    $exi_post_type_meta_url,
                    $selected_post_type,
                    $items_per_page,
                    $offset
                );

                $results = $wpdb->get_results($query);

                if (!empty($results)) :
                    $post_type_obj = get_post_type_object($selected_post_type);
                ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Title', 'ead-xml-importer'); ?></th>
                                <th width="200"><?php _e('', 'ead-xml-importer'); ?></th>
                                <th width="100"><?php _e('Actions', 'ead-xml-importer'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $post) : ?>
                                <tr>
                                    <td><?php echo esc_html($post->post_title); ?></td>
                                    <td></td>
                                    <td>
                                        <a href="<?php echo esc_url(get_edit_post_link($post->ID)); ?>"
                                            class="button button-small"
                                            target="_blank">
                                            <?php _e('Edit', 'ead-xml-importer'); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if ($total_pages > 1) : ?>
                        <div class="tablenav-pages">
                            <span class="displaying-num">
                                <?php printf(
                                    _n('%s item', '%s items', $total_items, 'ead-xml-importer'),
                                    number_format_i18n($total_items)
                                ); ?>
                            </span>
                            <?php
                            echo paginate_links(array(
                                'base' => add_query_arg('paged', '%#%'),
                                'format' => '',
                                'prev_text' => __('&laquo;'),
                                'next_text' => __('&raquo;'),
                                'total' => $total_pages,
                                'current' => $current_page,
                                'type' => 'list'
                            ));
                            ?>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>
            </div>
        </div>
    </div>
</div>