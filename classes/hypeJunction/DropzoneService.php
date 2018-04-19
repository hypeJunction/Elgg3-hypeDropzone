<?php

namespace hypeJunction;

use Elgg\Request;
use ElggFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DropzoneService {

	/**
	 * dropzone/upload action handler
	 *
	 * @param Request $request Request
	 *
	 * @return array
	 */
	public function handleUploads(Request $request) {

		$subtype = $request->getParam('subtype');
		if (!$subtype) {
			$subtype = elgg_get_plugin_setting('default_upload_subtype', 'hypeDropzone', 'file');
		}

		$uploads = $this->saveUploadedFiles('dropzone', [
			'owner_guid' => elgg_get_logged_in_user_guid(),
			'container_guid' => $request->getParam('container_guid') ? : ELGG_ENTITIES_ANY_VALUE,
			'subtype' => $subtype,
			'access_id' => ACCESS_PRIVATE,
			'origin' => $request->getParam('origin', 'dropzone'),
		]);

		$output = [];

		foreach ($uploads as $upload) {

			$messages = [];
			$success = true;

			if ($upload->error) {
				$messages[] = $upload->error;
				$success = false;
				$guid = false;
			} else {
				$file = $upload->file;
				$guid = $file->guid;
				$html = elgg_view('input/hidden', [
					'name' => $request->getParam('input_name', 'guids[]'),
					'value' => $file->guid,
				]);
			}

			$file_output = [
				'messages' => $messages,
				'success' => $success,
				'guid' => $guid,
				'html' => $html,
			];

			$output[] = elgg_trigger_plugin_hook('upload:after', 'dropzone', [
				'upload' => $upload,
			], $file_output);
		}

		return $output;
	}

	/**
	 * Returns an array of uploaded file objects regardless of upload status/errors
	 *
	 * @param string $input_name Form input name
	 *
	 * @return UploadedFile[]
	 */
	protected function getUploadedFiles($input_name) {
		$file_bag = _elgg_services()->request->files;
		if (!$file_bag->has($input_name)) {
			return [];
		}

		$files = $file_bag->get($input_name);
		if (!$files) {
			return [];
		}
		if (!is_array($files)) {
			$files = [$files];
		}

		return $files;
	}

	/**
	 * Save uploaded files
	 *
	 * @param string $input_name Form input name
	 * @param array  $attributes File attributes
	 *
	 * @return ElggFile[]
	 */
	protected function saveUploadedFiles($input_name, array $attributes = []) {

		$files = [];

		$uploaded_files = $this->getUploadedFiles($input_name);

		$subtype = elgg_extract('subtype', $attributes, 'file', false);
		unset($attributes['subtype']);

		$class = elgg_get_entity_class('object', $subtype);
		if (!$class || !class_exists($class) || !is_subclass_of($class, ElggFile::class)) {
			$class = ElggFile::class;
		}

		foreach ($uploaded_files as $upload) {
			if (!$upload->isValid()) {
				$error = new \stdClass();
				$error->error = elgg_get_friendly_upload_error($upload->getError());
				$files[] = $error;
				continue;
			}

			$file = new $class();
			/* @var $file ElggFile */
			$file->subtype = $subtype;
			foreach ($attributes as $key => $value) {
				$file->$key = $value;
			}

			$uploaded = $file->acceptUploadedFile($upload);

			if (!$uploaded) {
				$error = new \stdClass();
				$error->error = elgg_echo('dropzone:file_not_entity');
				$files[] = $error;
				continue;
			}

			if (!$file->save() || !$file->exists()) {
				$file->delete();
				$error = new \stdClass();
				$error->error = elgg_echo('dropzone:file_not_entity');
				$files[] = $error;
				continue;
			}

			$file->saveIconFromElggFile($file);

			$success = new \stdClass();
			$success->file = $file;
			$files[] = $success;
		}

		return $files;
	}

}
