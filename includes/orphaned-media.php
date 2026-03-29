<?php

function sc_get_orphaned_media_absolute_path($attached_file) {
	$attached_file = is_string($attached_file) ? trim($attached_file) : '';

	if ($attached_file === '') {
		return '';
	}

	if (function_exists('path_is_absolute') && path_is_absolute($attached_file)) {
		return wp_normalize_path($attached_file);
	}

	$uploads = wp_get_upload_dir();

	if (!empty($uploads['error']) || empty($uploads['basedir'])) {
		return '';
	}

	return wp_normalize_path(path_join($uploads['basedir'], ltrim($attached_file, '/\\')));
}

function sc_get_orphaned_media_items() {
	global $wpdb;

	$rows = $wpdb->get_results(
		"SELECT p.ID, p.post_title, p.post_mime_type, pm.meta_value AS attached_file
		 FROM {$wpdb->posts} p
		 LEFT JOIN {$wpdb->postmeta} pm
		 	ON pm.post_id = p.ID
		 	AND pm.meta_key = '_wp_attached_file'
		 WHERE p.post_type = 'attachment'
		   AND p.post_status <> 'trash'
		 ORDER BY p.ID DESC"
	);

	if (empty($rows)) {
		return array();
	}

	$items = array();

	foreach ($rows as $row) {
		$attachment_id = absint($row->ID);
		$attached_file = is_string($row->attached_file) ? trim($row->attached_file) : '';
		$file_path = sc_get_orphaned_media_absolute_path($attached_file);
		$is_missing = $attached_file === '' || $file_path === '' || !file_exists($file_path);

		if (!$is_missing) {
			continue;
		}

		$items[] = (object) array(
			'ID' => $attachment_id,
			'post_title' => get_the_title($attachment_id),
			'post_mime_type' => (string) $row->post_mime_type,
			'attached_file' => $attached_file,
			'file_path' => $file_path,
			'file_url' => (string) wp_get_attachment_url($attachment_id),
			'reason' => $attached_file === ''
				? 'Missing _wp_attached_file metadata.'
				: 'Original file is missing from the uploads directory.',
		);
	}

	return $items;
}

function sc_delete_orphaned_media_items($attachment_ids) {
	$attachment_ids = array_unique(array_filter(array_map('absint', (array) $attachment_ids)));

	if (empty($attachment_ids)) {
		return 0;
	}

	$deleted = 0;

	foreach ($attachment_ids as $attachment_id) {
		if (wp_delete_attachment($attachment_id, true)) {
			$deleted++;
		}
	}

	return $deleted;
}

function sc_render_orphaned_media_table($rows) {
	if (empty($rows)) {
		echo '<p>No orphaned media items found for this view.</p>';
		return;
	}

	echo '<table class="widefat striped" style="max-width:100%;">';
	echo '<thead><tr>';
	echo '<th>Attachment ID</th>';
	echo '<th>Title</th>';
	echo '<th>MIME Type</th>';
	echo '<th>Missing File URL</th>';
	echo '<th>Attached File Meta</th>';
	echo '<th>Expected Path</th>';
	echo '<th>Reason</th>';
	echo '</tr></thead><tbody>';

	foreach ($rows as $row) {
		echo '<tr>';
		echo '<td>' . intval($row->ID) . '</td>';
		echo '<td>' . esc_html($row->post_title ?: '(no title)') . '</td>';
		echo '<td>' . esc_html($row->post_mime_type ?: 'unknown') . '</td>';
		echo '<td><code style="white-space:pre-wrap;word-break:break-word;display:block;max-width:320px;">' . esc_html($row->file_url ?: '(no url)') . '</code></td>';
		echo '<td><code style="white-space:pre-wrap;word-break:break-word;display:block;max-width:320px;">' . esc_html($row->attached_file ?: '(empty)') . '</code></td>';
		echo '<td><code style="white-space:pre-wrap;word-break:break-word;display:block;max-width:320px;">' . esc_html($row->file_path ?: '(unable to resolve path)') . '</code></td>';
		echo '<td>' . esc_html($row->reason) . '</td>';
		echo '</tr>';
	}

	echo '</tbody></table>';
}

function sc_render_orphaned_media_pagination($page, $total_pages, $page_arg, $query_args = array()) {
	if ($total_pages <= 1) {
		return;
	}

	echo '<div style="margin-top:20px; overflow:hidden;">';
	for ($i = 1; $i <= $total_pages; $i++) {
		$url_args = array_merge($query_args, array($page_arg => $i));
		$url = esc_url(add_query_arg($url_args));

		if ($i === $page) {
			echo '<span style="font-weight:bold;padding:0 5px;">' . $i . '</span>';
		} else {
			echo '<a href="' . $url . '" style="padding:0 5px;">' . $i . '</a>';
		}
	}
	echo '</div>';
}

function sc_handle_orphaned_media_action() {
	if (!is_admin()) {
		return;
	}

	if (!current_user_can('manage_options')) {
		return;
	}

	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		return;
	}

	$page_slug = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';

	if ($page_slug !== SC_ORPHANED_MEDIA_MENU_SLUG) {
		return;
	}

	if (!isset($_POST['sc_orphaned_media_action'])) {
		return;
	}

	check_admin_referer('sc_orphaned_media_action');

	$per_page = 50;
	$all_items = sc_get_orphaned_media_items();
	$action = sanitize_key(wp_unslash($_POST['sc_orphaned_media_action']));
	$attachment_ids = array();
	$page = isset($_POST['sc_orphaned_media_page']) ? max(1, intval($_POST['sc_orphaned_media_page'])) : 1;

	if ($action === 'delete_batch') {
		$batch_offset = ($page - 1) * $per_page;
		$attachment_ids = wp_list_pluck(array_slice($all_items, $batch_offset, $per_page), 'ID');
	}

	if ($action === 'delete_all') {
		$attachment_ids = wp_list_pluck($all_items, 'ID');
	}

	$deleted = sc_delete_orphaned_media_items($attachment_ids);
	$redirect_url = add_query_arg(
		array(
			'page' => SC_ORPHANED_MEDIA_MENU_SLUG,
			'sc_orphaned_media_page' => $page,
			'sc_orphaned_media_notice' => $action,
			'sc_deleted' => $deleted,
		),
		admin_url('admin.php')
	);

	wp_safe_redirect($redirect_url);
	exit;
}
add_action('admin_init', 'sc_handle_orphaned_media_action');

function sc_find_orphaned_media_page() {
	if (!current_user_can('manage_options')) {
		return;
	}

	$page = isset($_GET['sc_orphaned_media_page']) ? max(1, intval($_GET['sc_orphaned_media_page'])) : 1;
	$per_page = 50;

	$all_items = sc_get_orphaned_media_items();
	$total_items = count($all_items);
	$total_pages = max(1, (int) ceil($total_items / $per_page));
	$page = min($page, $total_pages);
	$offset = ($page - 1) * $per_page;
	$rows = array_slice($all_items, $offset, $per_page);
	$notice = isset($_GET['sc_orphaned_media_notice']) ? sanitize_key(wp_unslash($_GET['sc_orphaned_media_notice'])) : '';
	$deleted = isset($_GET['sc_deleted']) ? absint($_GET['sc_deleted']) : 0;

	echo '<div class="wrap">';
	echo '<h1>Orphaned Media</h1>';
	echo '<p>This tool finds media library attachments that still exist in the WordPress database but whose original upload file is missing from the uploads directory.</p>';

	if ($notice === 'delete_batch') {
		echo '<div class="notice notice-success is-dismissible"><p>Deleted <strong>' . intval($deleted) . '</strong> orphaned media items from this batch.</p></div>';
	}

	if ($notice === 'delete_all') {
		echo '<div class="notice notice-success is-dismissible"><p>Deleted <strong>' . intval($deleted) . '</strong> orphaned media items across all pages.</p></div>';
	}

	echo '<p>Total orphaned media items: <strong>' . intval($total_items) . '</strong></p>';

	echo '<form method="post" style="display:inline-block;margin-right:10px;margin-bottom:20px;">';
	wp_nonce_field('sc_orphaned_media_action');
	echo '<input type="hidden" name="sc_orphaned_media_page" value="' . esc_attr($page) . '">';
	echo '<input type="hidden" name="sc_orphaned_media_action" value="delete_batch">';
	echo '<input type="submit" class="button button-secondary" value="Delete This Batch" onclick="return confirm(\'Are you sure? This will permanently delete the orphaned media items shown on this page.\')">';
	echo '</form>';

	echo '<form method="post" style="display:inline-block;margin-bottom:20px;">';
	wp_nonce_field('sc_orphaned_media_action');
	echo '<input type="hidden" name="sc_orphaned_media_page" value="' . esc_attr($page) . '">';
	echo '<input type="hidden" name="sc_orphaned_media_action" value="delete_all">';
	echo '<input type="submit" class="button button-danger" value="Delete All Orphaned Media" onclick="return confirm(\'Are you sure? This will permanently delete all orphaned media items from the media library.\')">';
	echo '</form>';

	sc_render_orphaned_media_table($rows);
	sc_render_orphaned_media_pagination($page, $total_pages, 'sc_orphaned_media_page', array(
		'page' => SC_ORPHANED_MEDIA_MENU_SLUG,
	));

	echo '</div>';
}
