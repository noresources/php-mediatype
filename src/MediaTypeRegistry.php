<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package MediaType
 */
namespace NoreSources\MediaType;

use NoreSources\SingletonTrait;
use NoreSources\Container\Container;
use NoreSources\Type\TypeDescription;

/**
 * IANA registered media types
 */
class MediaTypeRegistry
{
	use SingletonTrait;

	/**
	 *
	 * @param MediaTypeInterface|string $mediaType
	 * @return boolean
	 */
	public function isRegistered($mediaType)
	{
		$type = null;
		if ($mediaType instanceof MediaTypeInterface)
		{
			$type = $mediaType->getType();
			$mediaType = \strval($mediaType);
		}
		else
		{
			$mediaType = \strval($mediaType);
			$p = \strpos($mediaType, '/');
			if ($p === false)
				throw new \InvalidArgumentException(
					$mediaType . ' is not a valid Media Type');
			$type = \substr($mediaType, 0, $p);
		}

		$types = $this->getTypeList($type);
		return Container::valueExists($types, $mediaType);
	}

	/**
	 *
	 * @param string $mainType
	 *        	Media type main type
	 * @return string[]
	 */
	public function getTypeList($mainType)
	{
		if (!isset($this->typeMap))
		{
			$this->typeMap = [];
		}

		if (!isset($this->typeMap[$mainType]))
		{
			$this->typeMap[$mainType] = [];
			$filename = __DIR__ . '/' .
				TypeDescription::getLocalName($this) . '/' . $mainType .
				'.json';
			if (\file_exists($filename))
				$this->typeMap[$mainType] = \json_decode(
					\file_get_contents($filename), true);
		}

		return $this->typeMap[$mainType];
	}

	/**
	 *
	 * @var array
	 */
	private $typeMap;
}
