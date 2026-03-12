<?php

// ================================
// ADMIN PAGE: FIND ORPHANED POSTMETA
// ================================

function sc_get_orphaned_postmeta_total($meta_key = null) {
	global $wpdb;

	if ($meta_key !== null) {
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				 FROM {$wpdb->postmeta} pm
				 LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				 WHERE p.ID IS NULL
				   AND pm.meta_key = %s",
				$meta_key
			)
		);
	}

	return (int) $wpdb->get_var(
		"SELECT COUNT(*)
		 FROM {$wpdb->postmeta} pm
		 LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		 WHERE p.ID IS NULL"
	);
}

function sc_get_orphaned_postmeta_rows($limit, $offset, $meta_key = null) {
	global $wpdb;

	if ($meta_key !== null) {
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT pm.meta_id, pm.post_id, pm.meta_key, pm.meta_value
				 FROM {$wpdb->postmeta} pm
				 LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				 WHERE p.ID IS NULL
				   AND pm.meta_key = %s
				 ORDER BY pm.meta_id DESC
				 LIMIT %d OFFSET %d",
				$meta_key,
				$limit,
				$offset
			)
		);
	}

	return $wpdb->get_results(
		$wpdb->prepare(
			"SELECT pm.meta_id, pm.post_id, pm.meta_key, pm.meta_value
			 FROM {$wpdb->postmeta} pm
			 LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			 WHERE p.ID IS NULL
			 ORDER BY pm.meta_id DESC
			 LIMIT %d OFFSET %d",
			$limit,
			$offset
		)
	);
}

function sc_delete_orphaned_postmeta($meta_key = null) {
	global $wpdb;

	if ($meta_key !== null) {
		return (int) $wpdb->query(
			$wpdb->prepare(
				"DELETE pm
				 FROM {$wpdb->postmeta} pm
				 LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				 WHERE p.ID IS NULL
				   AND pm.meta_key = %s",
				$meta_key
			)
		);
	}

	return (int) $wpdb->query(
		"DELETE pm
		 FROM {$wpdb->postmeta} pm
		 LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		 WHERE p.ID IS NULL"
	);
}

function sc_delete_orphaned_postmeta_batch($limit, $offset, $meta_key = null) {
	global $wpdb;

	$rows = sc_get_orphaned_postmeta_rows($limit, $offset, $meta_key);

	if (empty($rows)) {
		return 0;
	}

	$meta_ids = array_map('intval', wp_list_pluck($rows, 'meta_id'));
	$placeholders = implode(',', array_fill(0, count($meta_ids), '%d'));

	return (int) $wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->postmeta}
			 WHERE meta_id IN ($placeholders)",
			$meta_ids
		)
	);
}

function sc_render_orphaned_postmeta_table($rows) {
	if (empty($rows)) {
		echo '<p>No rows found for this view.</p>';
		return;
	}

	echo '<table class="widefat striped" style="max-width:100%;">';
	echo '<thead><tr>';
	echo '<th>Meta ID</th>';
	echo '<th>Post ID</th>';
	echo '<th>Meta Key</th>';
	echo '<th>Meta Value</th>';
	echo '</tr></thead><tbody>';

	foreach ($rows as $row) {
		echo '<tr>';
		echo '<td>' . intval($row->meta_id) . '</td>';
		echo '<td>' . intval($row->post_id) . '</td>';
		echo '<td>' . esc_html($row->meta_key) . '</td>';
		echo '<td><code style="white-space:pre-wrap;word-break:break-word;display:block;max-width:800px;">' . esc_html(wp_html_excerpt((string) $row->meta_value, 300, '...')) . '</code></td>';
		echo '</tr>';
	}

	echo '</tbody></table>';
}

function sc_render_orphaned_postmeta_pagination($page, $total_pages, $page_arg) {
	if ($total_pages <= 1) {
		return;
	}

	echo '<div style="margin-top:20px; overflow: hidden;">';
	for ($i = 1; $i <= $total_pages; $i++) {
		$url = esc_url(add_query_arg($page_arg, $i));
		if ($i === $page) {
			echo '<span style="font-weight:bold;padding:0 5px;">' . $i . '</span>';
		} else {
			echo '<a href="' . $url . '" style="padding:0 5px;">' . $i . '</a>';
		}
	}
	echo '</div>';
}

function sc_find_orphaned_postmeta_page() {
	global $wpdb;

	if (!current_user_can('manage_options')) {
		return;
	}

	$all_page = isset($_GET['sc_orphaned_postmeta_all_page']) ? max(1, intval($_GET['sc_orphaned_postmeta_all_page'])) : 1;
	$per_page = 50;
	$all_offset = ($all_page - 1) * $per_page;

	echo '<div class="wrap">';
	echo '<h1>Orphaned Post Meta</h1>';
	echo '<p>This tool finds rows in <code>' . esc_html($wpdb->postmeta) . '</code> whose <code>post_id</code> no longer exists in <code>' . esc_html($wpdb->posts) . '</code>.</p>';

	if (isset($_POST['sc_delete_all_orphaned_postmeta']) && check_admin_referer('sc_delete_all_orphaned_postmeta_action')) {
		$deleted = sc_delete_orphaned_postmeta();
		echo '<div class="notice notice-success is-dismissible"><p>Deleted <strong>' . intval($deleted) . '</strong> orphaned postmeta rows across all meta keys.</p></div>';
	}

	if (isset($_POST['sc_delete_all_orphaned_postmeta_batch']) && check_admin_referer('sc_delete_all_orphaned_postmeta_action')) {
		$batch_page = isset($_POST['sc_orphaned_postmeta_all_page']) ? max(1, intval($_POST['sc_orphaned_postmeta_all_page'])) : 1;
		$batch_offset = ($batch_page - 1) * $per_page;
		$deleted = sc_delete_orphaned_postmeta_batch($per_page, $batch_offset);
		echo '<div class="notice notice-success is-dismissible"><p>Deleted <strong>' . intval($deleted) . '</strong> orphaned postmeta rows from this batch.</p></div>';
	}

	$all_total = sc_get_orphaned_postmeta_total();
	$all_rows = sc_get_orphaned_postmeta_rows($per_page, $all_offset);
	$all_total_pages = (int) ceil($all_total / $per_page);

	echo '<hr>';
	echo '<h2>Orphaned Postmeta</h2>';
	echo '<p>Total orphaned rows across all meta keys: <strong>' . intval($all_total) . '</strong></p>';

	echo '<form method="post" style="display:inline-block;margin-right:10px;margin-bottom:20px;">';
	wp_nonce_field('sc_delete_all_orphaned_postmeta_action');
	echo '<input type="hidden" name="sc_orphaned_postmeta_all_page" value="' . esc_attr($all_page) . '">';
	echo '<input type="submit" name="sc_delete_all_orphaned_postmeta_batch" class="button button-secondary" value="Delete This Batch" onclick="return confirm(\'Are you sure? This will permanently delete the current batch of orphaned postmeta rows across all meta keys.\')">';
	echo '</form>';

	echo '<form method="post" style="display:inline-block;margin-bottom:20px;">';
	wp_nonce_field('sc_delete_all_orphaned_postmeta_action');
	echo '<input type="submit" name="sc_delete_all_orphaned_postmeta" class="button button-danger" value="Delete All Orphaned Postmeta" onclick="return confirm(\'Are you sure? This will permanently delete all orphaned postmeta rows.\')">';
	echo '</form>';

	sc_render_orphaned_postmeta_table($all_rows);
	sc_render_orphaned_postmeta_pagination($all_page, $all_total_pages, 'sc_orphaned_postmeta_all_page');

	echo '</div>';
}

function sc_register_orphaned_postmeta_page() {
	add_submenu_page(
		'tools.php',
		'Orphaned Postmeta',
		'Orphaned Postmeta',
		'manage_options',
		'sc-orphaned-postmeta',
		'sc_find_orphaned_postmeta_page'
	);
}
add_action('admin_menu', 'sc_register_orphaned_postmeta_page');
