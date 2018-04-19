<?php

namespace hypeJunction\Dropzone;

use Elgg\BadRequestException;
use Elgg\Http\ResponseBuilder;
use Elgg\HttpException;
use Elgg\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ChunkUploadAction {

	/**
	 * Store file chunk
	 *
	 * @param Request $request Request
	 *
	 * @return ResponseBuilder
	 * @throws HttpException
	 */
	public function __invoke(Request $request) {

		$index = $request->getParam('chunk_index');
		$uuid = $request->getParam('uuid');
		$chunk_size = $request->getParam('chunk_size');

		$user = elgg_get_logged_in_user_entity();

		try {
			$uploads = elgg_get_uploaded_files('dropzone');
			if (empty($uploads)) {
				throw new BadRequestException('Uploads are empty');
			}

			$upload = array_shift($uploads);

			if (!$upload instanceof UploadedFile) {
				throw new BadRequestException('Not a valid upload file');
			}

			if (!$upload->isValid()) {
				throw new BadRequestException(elgg_get_friendly_upload_error($upload->getError()));
			}

			if ($upload->getClientSize() != $chunk_size) {
				throw new BadRequestException("Chunk has been truncated from $chunk_size to {$upload->getSize()}");
			}

			$chunk = new FileChunk();
			$chunk->owner_guid = $user->guid;
			$chunk->setFilename("chunks/$uuid/$index");

			$chunk->open('write');
			$chunk->close();

			try {
				$filestorename = $chunk->getFilenameOnFilestore();
				$upload->move(pathinfo($filestorename, PATHINFO_DIRNAME), pathinfo($filestorename, PATHINFO_BASENAME));
			} catch (FileException $ex) {
				throw new HttpException($ex->getMessage(), ELGG_HTTP_INTERNAL_SERVER_ERROR);
			}
		} catch (HttpException $ex) {
			return elgg_error_response($ex->getMessage(), REFERRER, $ex->getCode());
		}

		return elgg_ok_response([
			'chunk' => $chunk,
			'size' => $chunk->getSize(),
		]);
	}
}