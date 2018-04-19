<?php

namespace hypeJunction\Dropzone;

class FileChunk extends \ElggFile {

	const SUBTYPE = 'file_chunk';

	public function initializeAttributes() {
		parent::initializeAttributes();
		$this->attributes['subtype'] = self::SUBTYPE;
	}
}