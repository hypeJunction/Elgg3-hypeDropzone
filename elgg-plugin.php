<?php

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
];