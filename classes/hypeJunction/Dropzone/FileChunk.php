<?php

namespace hypeJunction\Dropzone;

/**
 * ElggFile subclass representing one chunk in a chunked upload.
 */
class FileChunk extends \ElggFile {

	const SUBTYPE = 'file_chunk';

	/**
	 * {@inheritdoc}
	 */
	public function initializeAttributes() {
		parent::initializeAttributes();
		$this->attributes['subtype'] = self::SUBTYPE;
	}
}
