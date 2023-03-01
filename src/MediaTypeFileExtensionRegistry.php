<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources\MediaType;

use NoreSources\SingletonTrait;
use NoreSources\Container\Container;

/**
 * Associate Media Types and their commonly used file extensions.
 *
 * Base on Apache httpd public list
 *
 * @see https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types
 *
 */
class MediaTypeFileExtensionRegistry
{

	use SingletonTrait;

	/**
	 *
	 * @param string $extension
	 * @return MediaType|false The media type commonly associated with the given file extension or
	 *         false
	 *         if the extension is not recognized.
	 */
	public function mediaTypeFromExtension($extension)
	{
		if (!isset($this->extensionMap))
		{
			$this->extensionMap = self::getData('extensions');
		}

		$mediaType = Container::keyValue($this->extensionMap, $extension,
			false);
		if ($mediaType)
			return MediaType::createFromString($mediaType, true);

		return $mediaType;
	}

	/**
	 * Get the extension(s) commonly used with the given Media Type
	 *
	 * @param MediaType $mediaType
	 * @return string[] List of extensions
	 */
	public static function getMediaTypeExtensions(MediaType $mediaType)
	{
		if (!isset($this->typesMap))
			$this->typesMap = [];

		if (!Container::keyExists($this->typesMap, $mediaType->getType()))
			$this->typesMap[$mediaType->getType()] = self::getData(
				'types.' . $mediaType->getType());

		return Container::keyValue(
			$this->typesMap[$mediaType->getType()], []);
	}

	private function getData($suffix)
	{
		$filename = __DIR__ . '/' . basename(__FILE__, '.php') . '/' .
			$suffix . '.json';
		if (!\file_exists($filename))
			throw new \InvalidArgumentException(
				$filename . ' not found');

		return \json_decode(\file_get_contents($filename), true);
	}

	private $typesMap;

	private $extensionMap;
}