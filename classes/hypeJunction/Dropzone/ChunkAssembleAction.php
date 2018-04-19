<?php

namespace hypeJunction\Dropzone;

use Elgg\BadRequestException;
use Elgg\Http\ResponseBuilder;
use Elgg\HttpException;
use Elgg\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ChunkAssembleAction {

	/**
	 * Assemble file chunks
	 *
	 * @param Request $request Request
	 *
	 * @return ResponseBuilder
	 * @throws HttpException
	 * @throws \IOException
	 * @throws \InvalidParameterException
	 */
	public function __invoke(Request $request) {

		$uuid = $request->getParam('uuid');
		$count = $request->getParam('chunk_count');
		$file_name = $request->getParam('file_name');
		$file_size = $request->getParam('file_size');

		$user = elgg_get_logged_in_user_entity();

		$subtype = $request->getParam('subtype', 'file');
		$class = elgg_get_entity_class('object', $subtype);
		if (!is_subclass_of($class, \ElggFile::class)) {
			$class = \ElggFile::class;
		}

		$file = new $class();
		/* @var $file \ElggFile */

		$file->owner_guid = $user->guid;
		$file->container_guid = $request->getParam('container_guid') ? : null;
		$file->subtype = $subtype;
		$file->access_id = ACCESS_PRIVATE;
		$file->origin = 'dropzone';

		$file->originalfilename = $file_name;
		if (empty($file->title)) {
			$file->title = htmlspecialchars($file->originalfilename, ENT_QUOTES, 'UTF-8');
		}

		$file->upload_time = time();
		$prefix = $file->filestore_prefix ? : 'file';
		$prefix = trim($prefix, '/');
		$filename = elgg_strtolower("$prefix/{$file->upload_time}{$file->originalfilename}");
		$file->setFilename($filename);
		$file->filestore_prefix = $prefix;

		$file->open('write');
		$file->close();

		for ($index = 0; $index < $count; $index++) {
			$chunk = new FileChunk();
			$chunk->owner_guid = $user->guid;
			$chunk->setFilename("chunks/$uuid/$index");

			$blob = file_get_contents($chunk->getFilenameOnFilestore());
			file_put_contents($file->getFilenameOnFilestore(), $blob, FILE_APPEND);
		}

		$dir = new \ElggFile();
		$dir->owner_guid = $user->guid;
		$dir->setFilename("chunks/$uuid");

		_elgg_rmdir($dir->getFilenameOnFilestore());

		$error = false;
		if (!$file->exists()) {
			$error = 'Could not write file';
		} else if (!$file->save()) {
			$error = 'Can not save file';
		}

		if ($error) {
			$file->delete();
			throw new HttpException($error, ELGG_HTTP_INTERNAL_SERVER_ERROR);
		}

		$html = elgg_view('input/hidden', [
			'name' => $request->getParam('input_name', 'guids[]'),
			'value' => $file->guid,
		]);

		$file_output = [
			'messages' => [],
			'success' => true,
			'guid' => $file->guid,
			'html' => $html,
		];

		$output = json_encode([$file_output]);

		elgg_register_event_handler('shutdown', 'system', function() use ($file) {
			if ($this->hasMemoryToResize($file->getFilenameOnFilestore())) {
				$file->saveIconFromElggFile($file);
			}
		});

		return elgg_ok_response($output);
	}

	/**
	 * Do we estimate that we have enough memory available to resize an image?
	 *
	 * @param string $source - the source path of the file
	 *
	 * @return bool
	 * @access private
	 */
	protected function hasMemoryToResize($source) {
		$imginfo = getimagesize($source);
		$requiredMemory1 = ceil($imginfo[0] * $imginfo[1] * 5.35);
		$requiredMemory2 = ceil($imginfo[0] * $imginfo[1] * ($imginfo['bits'] / 8) * $imginfo['channels'] * 2.5);
		$requiredMemory = (int) max($requiredMemory1, $requiredMemory2);

		$mem_avail = elgg_get_ini_setting_in_bytes('memory_limit');
		$mem_used = memory_get_usage();

		$mem_avail = $mem_avail - $mem_used - 20*1024*1024;

		return $mem_avail > $requiredMemory;
	}
}