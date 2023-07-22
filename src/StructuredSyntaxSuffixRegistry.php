<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package MediaType
 */
namespace NoreSources\MediaType;

use NoreSources\SingletonTrait;

/**
 * List of
 *
 * @see https://www.iana.org/assignments/media-type-structured-suffix/media-type-structured-suffix.xhtml
 *
 */
class StructuredSyntaxSuffixRegistry
{

	use SingletonTrait;

	/**
	 * Indicates if the given string is a registered structured syntax suffix
	 *
	 * @param string $suffix
	 *        	Structured syntax suffix. Optioaly prefixed with '+'
	 * @return boolean true if the given string is a registered structured syntax suffix
	 */
	public function isRegistered($suffix)
	{
		self::initialize();
		if (\substr($suffix, 0, 1) == '+')
			$suffix = \substr($suffix, 1);

		return \array_key_exists($suffix, $this->suffixes);
	}

	/**
	 * Initialize the suffixes table
	 */
	private function initialize()
	{
		if (!\is_array($this->suffixes))
		{
			$this->suffixes = [ /* Auto-generated code --<<sufixes>>--*/'ber' => 'Basic Encoding Rules (BER) message transfer syntax', 
				'cbor' => 'Concise Binary Object Representation (CBOR)', 
				'cbor-seq' => 'CBOR Sequence', 
				'der' => 'Distinguished Encoding Rules (DER) message transfer syntax', 
				'fastinfoset' => 'Fast Infoset document format', 
				'gzip' => 'gzip file storage and transfer format', 
				'json' => 'JavaScript Object Notation (JSON)', 
				'json-seq' => 'JSON Text Sequence', 
				'jwt' => 'JSON Web Token (JWT)', 
				'sqlite3' => 'SQLite3 database', 
				'tlv' => 'Type Length Value', 
				'wbxml' => 'WAP Binary XML (WBXML) document format', 
				'xml' => 'Extensible Markup Language (XML)', 
				'yaml' => 'YAML Ain\'t Markup Language (YAML)', 
				'zip' => 'ZIP file storage and transfer format', 
				'zstd' => 'Zstandard'/*--<</sufixes>>-- */
			];
		}
	}

	private $suffixes;
}