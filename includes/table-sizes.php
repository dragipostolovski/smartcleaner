<?php

function sc_get_table_size_order() {
	$order = isset($_GET['sc_table_size_order']) ? sanitize_key(wp_unslash($_GET['sc_table_size_order'])) : 'desc';

	return $order === 'asc' ? 'asc' : 'desc';
}

function sc_get_database_table_sizes($order = 'desc') {
	global $wpdb;

	$order_sql = $order === 'asc' ? 'ASC' : 'DESC';

	return $wpdb->get_results(
		$wpdb->prepare(
			"SELECT table_name,
			        table_type,
			        engine,
			        table_rows,
			        data_length,
			        index_length,
			        (COALESCE(data_length, 0) + COALESCE(index_length, 0)) AS total_size
			 FROM information_schema.TABLES
			 WHERE table_schema = %s
			 ORDER BY total_size {$order_sql}, table_name ASC",
			DB_NAME
		)
	);
}

function sc_format_table_size_bytes($bytes) {
	$bytes = max(0, (int) $bytes);

	if (function_exists('size_format')) {
		return size_format($bytes, 2);
	}

	return number_format_i18n($bytes) . ' bytes';
}

function sc_render_table_sizes_table($rows, $order) {
	if (empty($rows)) {
		echo '<p>No database tables found.</p>';
		return;
	}

	$next_order = $order === 'asc' ? 'desc' : 'asc';
	$size_url = add_query_arg(
		array(
			'page' => SC_TABLE_SIZES_MENU_SLUG,
			'sc_table_size_order' => $next_order,
		),
		admin_url('admin.php')
	);
	$size_label = $order === 'asc' ? 'Size ↑' : 'Size ↓';

	echo '<table class="widefat striped" style="max-width:100%;">';
	echo '<thead><tr>';
	echo '<th>Table</th>';
	echo '<th>Type</th>';
	echo '<th>Engine</th>';
	echo '<th style="text-align:right;">Rows</th>';
	echo '<th style="text-align:right;">Data</th>';
	echo '<th style="text-align:right;">Index</th>';
	echo '<th style="text-align:right;"><a href="' . esc_url($size_url) . '">' . esc_html($size_label) . '</a></th>';
	echo '</tr></thead><tbody>';

	foreach ($rows as $row) {
		echo '<tr>';
		echo '<td><code>' . esc_html($row->table_name) . '</code></td>';
		echo '<td>' . esc_html($row->table_type ?: 'unknown') . '</td>';
		echo '<td>' . esc_html($row->engine ?: 'n/a') . '</td>';
		echo '<td style="text-align:right;">' . esc_html(number_format_i18n((int) $row->table_rows)) . '</td>';
		echo '<td style="text-align:right;">' . esc_html(sc_format_table_size_bytes($row->data_length)) . '</td>';
		echo '<td style="text-align:right;">' . esc_html(sc_format_table_size_bytes($row->index_length)) . '</td>';
		echo '<td style="text-align:right;"><strong>' . esc_html(sc_format_table_size_bytes($row->total_size)) . '</strong></td>';
		echo '</tr>';
	}

	echo '</tbody></table>';
}

function sc_render_table_sizes_page() {
	if (!current_user_can('manage_options')) {
		return;
	}

	$order = sc_get_table_size_order();
	$rows = sc_get_database_table_sizes($order);
	$total_size = 0;

	foreach ($rows as $row) {
		$total_size += (int) $row->total_size;
	}

	echo '<div class="wrap">';
	echo '<h1>Table Sizes</h1>';
	echo '<p>This tool lists all tables in the current database and shows their data, index, and total storage size.</p>';
	echo '<p>Database: <code>' . esc_html(DB_NAME) . '</code></p>';
	echo '<p>Total tables: <strong>' . intval(count($rows)) . '</strong> &nbsp; Total size: <strong>' . esc_html(sc_format_table_size_bytes($total_size)) . '</strong></p>';
	echo '<p>Sorted by size: <strong>' . esc_html(strtoupper($order)) . '</strong></p>';

	sc_render_table_sizes_table($rows, $order);

	echo '</div>';
}
