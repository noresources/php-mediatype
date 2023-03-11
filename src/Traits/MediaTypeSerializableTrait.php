<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package MediaType
 */
namespace NoreSources\MediaType\Traits;

use NoreSources\Container\Container;
use NoreSources\Http\ParameterMapSerializer;
use NoreSources\MediaType\MediaRange;
use NoreSources\MediaType\MediaSubType;
use NoreSources\MediaType\MediaTypeException;
use NoreSources\MediaType\MediaTypeInterface;

trait MediaTypeSerializableTrait
{

	/**
	 *
	 * @return string String representation of the media type and its optional parameters
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize()
	{
		return $this->serializeToString();
	}

	/**
	 *
	 * @return string Media type and parameter string representation
	 */
	public function serializeToString()
	{
		return self::serializeMediaTypeInterfaceToString($this);
	}

	/**
	 *
	 * @return string Media Type / Range and parameters
	 */
	public static function serializeMediaTypeInterfaceToString(
		MediaTypeInterface $mediaType)
	{
		$s = \strval($mediaType);
		if ($mediaType->getParameters()->count())
			$s .= '; ' .
				ParameterMapSerializer::serializeParameters(
					$mediaType->getParameters());
		return $s;
	}

	/**
	 *
	 * @param string $className
	 *        	MediaTypeInterface class
	 * @param string $pattern
	 *        	Media type string
	 * @param string $mediaTypeString
	 *        	Media type string
	 * @param boolean $withParameters
	 *        	Also parse parameters
	 * @throws MediaTypeException
	 * @return MediaTypeInterface
	 */
	public static function unserializeMediaTypeInterfaceInterface(
		$className, $pattern, $mediaTypeString, $withParameters)
	{
		$pattern = '^[\x9\x20]*' . $pattern;
		$matches = [];
		if (!\preg_match(chr(1) . $pattern . chr(1) . 'i',
			$mediaTypeString, $matches))
			throw new MediaTypeException($mediaTypeString,
				'Not a valid ' . $className . ' string');

		$subType = new MediaSubType([
			MediaRange::ANY
		], null);

		if (Container::keyExists($matches, 2) &&
			$matches[2] != MediaRange::ANY)
		{
			$facets = $matches[2];

			$length = \strlen($facets);
			$syntax = null;

			$lastPlus = \strrpos($facets, '+');
			if ($lastPlus !== false && $lastPlus < ($length - 1))
			{
				$syntax = \substr($facets, $lastPlus + 1);
				$facets = \substr($facets, 0, $lastPlus);
			}

			$subType = new MediaSubType(\explode('.', $facets), $syntax);
		}

		$cls = new \ReflectionClass($className);
		$mediaType = $cls->newInstance(
			Container::keyValue($matches, 1, MediaRange::ANY), $subType);
		if (!$withParameters)
			return $mediaType;

		$p = \strpos($mediaTypeString, ';');
		if ($p === false)
			return $mediaType;
		$parameters = [];
		ParameterMapSerializer::unserializeParameters($parameters,
			\trim(\substr($mediaTypeString, $p + 1)));
		$mediaType->setParameters($parameters);
		return $mediaType;
	}

	/**
	 *
	 * @deprecated Use jsonSerialize ()
	 */
	#[\ReturnTypeWillChange]
	public function serialize()
	{
		return $this->serializeToString();
	}

	/**
	 *
	 * @deprecated use createFromString ($text, true)
	 */
	#[\ReturnTypeWillChange]
	public function unserialize($serialized)
	{
		/** @var MediaTypeInterface $m */
		$m = \call_user_func([
			static::class,
			'createFromString'
		], $serialized, true);

		$this->mainType = $m->getType();
		$this->subType = $m->getSubType();
		$this->setParameters(
			Container::createArray($m->getParameters()));
	}
}
