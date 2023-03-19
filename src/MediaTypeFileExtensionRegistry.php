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

	public function __construct()
	{
		/*
		 * Workaround non-standard media type for YAML
		 */
		$this->setExtensionMediaType('yaml', 'text/yaml', true);
		$this->setExtensionMediaType('yml', 'text/yaml', true);
	}

	/**
	 *
	 * @param string $extension
	 * @return MediaType|false The media type commonly associated with the given file extension or
	 *         false
	 *         if the extension is not recognized.
	 */
	public function getExtensionMediaType($extension)
	{
		$mediaType = null;
		if (isset($this->userdefinedExtensionMap))
			$mediaType = Container::keyValue(
				$this->userdefinedExtensionMap, $extension);

		if (!$mediaType)
		{
			if (!isset($this->extensionMap))
				$this->extensionMap = self::getData('extensions');

			$mediaType = Container::keyValue($this->extensionMap,
				$extension, false);
		}

		if (!$mediaType)
			return false;

		if (!($mediaType instanceof MediaTypeInterface))
		{
			$mediaType = MediaType::createFromString($mediaType);
			$this->extensionMap[$extension] = $mediaType;
		}

		return $mediaType;
	}

	/**
	 *
	 * @deprecated Use getExtensionMediaType()
	 *
	 * @param string $extension
	 *        	Extension
	 * @return \NoreSources\MediaType\MediaType|\NoreSources\MediaType\false
	 */
	public function mediaTypeFromExtension($extension)
	{
		return $this->getExtensionMediaType($extension);
	}

	/**
	 * Get the extension(s) commonly used with the given Media Type
	 *
	 * @param MediaType $mediaType
	 * @return string[] List of extensions
	 */
	public function getMediaTypeExtensions(MediaType $mediaType)
	{
		$mainType = $mediaType->getType();
		$subType = \strval($mediaType->getSubType());

		if (isset($this->userdefinedTypesMap) &&
			Container::keyExists($this->userdefinedTypesMap, $mainType) &&
			Container::keyExists($this->userdefinedTypesMap[$mainType],
				$subType))
			return $this->userdefinedTypesMap[$mainType][$subType];
		{}

		if (!isset($this->typesMap))
			$this->typesMap = [];

		if (!Container::keyExists($this->typesMap, $mainType))
			$this->typesMap[$mainType] = self::getData(
				'types.' . $mainType);

		return Container::keyValue($this->typesMap[$mainType], $subType,
			[]);
	}

	public function setExtensionMediaType($extension, $mediaType,
		$reverseMap = true)
	{
		if (!isset($this->userdefinedExtensionMap))
			$this->userdefinedExtensionMap = [];

		$this->userdefinedExtensionMap[$extension] = $mediaType;

		if (!$reverseMap)
			return;

		$this->setMediaTypeExtension($mediaType, $extension, false);
	}

	public function setMediaTypeExtension($mediaType, $extension,
		$reverseMap = true)
	{
		$type = null;
		$subType = null;
		if ($mediaType instanceof MediaTypeInterface)
		{
			$type = $mediaType->getType();
			$subType = \strval($mediaType->getSubType());
		}
		else
		{
			$slash = \strpos($mediaType, '/');
			$type = \substr($mediaType, 0, $slash);
			$subType = \substr($mediaType, $slash + 1);
		}

		if (!isset($this->userdefinedTypesMap))
			$this->userdefinedTypesMap = [];
		if (!Container::keyExists($this->userdefinedTypesMap, $type))
			$this->userdefinedTypesMap[$type] = [];
		if (!Container::keyExists($this->userdefinedTypesMap[$type],
			$subType))
			$this->userdefinedTypesMap[$type][$subType] = [];

		$this->userdefinedTypesMap[$type][$subType][] = $extension;

		if (!$reverseMap)
			return;

		$this->setExtensionMediaType($extension, $mediaType, false);
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

	/**
	 *
	 * @var array
	 */
	private $typesMap;

	/**
	 *
	 * @var string[]
	 */
	private $extensionMap;

	/**
	 *
	 * @var string[]
	 */
	private $userdefinedExtensionMap;

	/**
	 *
	 * @var array
	 */
	private $userdefinedTypesMap;
}