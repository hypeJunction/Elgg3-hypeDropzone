<?php

$plugin_root = __DIR__;
$root = dirname(dirname($plugin_root));
$alt_root = dirname(dirname(dirname($root)));

if (file_exists("$plugin_root/vendor/autoload.php")) {
	$path = $plugin_root;
} else if (file_exists("$root/vendor/autoload.php")) {
	$path = $root;
} else {
	$path = $alt_root;
}

return [
	'entities' => [
		[
			'type' => 'object',
			'subtype' => 'file_chunk',
			'class' => \hypeJunction\Dropzone\FileChunk::class,
			'searchable' => false,
		],
	],
	'actions' => [
		'dropzone/upload' => [
			'controller' => \hypeJunction\Dropzone\UploadAction::class,
		],
		'dropzone/upload_chunk' => [
			'controller' => \hypeJunction\Dropzone\ChunkUploadAction::class,
		],
		'dropzone/assemble_chunks' => [
			'controller' => \hypeJunction\Dropzone\ChunkAssembleAction::class,
		],
	],
	'settings' => [
		'chunked_uploads' => true,
	],
	'views' => [
		'default' => [
			'dropzone/lib.js' => $path . '/vendor/npm-asset/dropzone/dist/min/dropzone-amd-module.min.js',
			'css/dropzone/stylesheet' => __DIR__ . '/views/default/dropzone/dropzone.css',
		],
	]
];