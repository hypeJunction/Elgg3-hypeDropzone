<?php

/**
 * Drag&Drop File Uploads
 *
 * @author Ismayil Khayredinov <info@hypejunction.com>
 * @copyright Copyright (c) 2015-2016, Ismayil Khayredinov
 */
require_once __DIR__ . '/autoloader.php';

elgg_register_event_handler('init', 'system', function() {

	// @see views.php for view locations
	elgg_extend_view('elgg.css', 'css/dropzone/stylesheet');
	elgg_extend_view('admin.css', 'css/dropzone/stylesheet');
});
