<?php

return [
	'plugin' => [
		'name' => 'hypeDropzone',
		'version' => '7.0.0',
	],

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
			'dropzone/lib.js' => __DIR__ . '/vendor/npm-asset/dropzone/dist/min/dropzone-amd-module.min.js',
			'css/dropzone/stylesheet' => __DIR__ . '/views/default/dropzone/dropzone.css',
		],
	],
	'view_extensions' => [
		'elgg.css' => [
			'css/dropzone/stylesheet' => [],
		],
		'admin.css' => [
			'css/dropzone/stylesheet' => [],
		],
	],
];