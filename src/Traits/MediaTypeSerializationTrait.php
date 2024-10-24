<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package MediaType
 */
namespace NoreSources\MediaType\Traits;

use NoreSources\Bitset;
use NoreSources\Container\Container;
use NoreSources\Http\ParameterMapSerializer;
use NoreSources\MediaType\MediaRange;
use NoreSources\MediaType\MediaSubType;
use NoreSources\MediaType\MediaTypeException;
use NoreSources\MediaType\MediaTypeInterface;

trait MediaTypeSerializationTrait
{

	/**
	 *
	 * @return string String representation of the media type and its optional parameters
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize()
	{
		return $this->toString();
	}

	/**
	 *
	 * @return string Media Type / Range and parameters
	 */
	public static function serializeMediaTypeInterfaceToString(
		MediaTypeInterface $mediaType,
		$partFlags = MediaTypeInterface::PART_ALL)
	{
		$parts = new Bitset($partFlags);
		$text = '';

		if ($parts->match(MediaTypeInterface::PART_MAINTYPE))
			$text .= $mediaType->getType();

		if ($parts->match(MediaTypeInterface::PART_SUBTYPE_FACETS) &&
			$mediaType->getSubType()->getFacetCount())
		{
			if (\strlen($text))
				$text .= '/';
			$text .= \implode('.', $mediaType->getSubType()->getFacets());
		}

		if ($parts->match(MediaTypeInterface::PART_SYNTAX_SUFFIX) &&
			!empty($mediaType->getSubType()->getStructuredSyntax()))
		{
			if (\strlen($text))
				$text .= '+';
			$text .= $mediaType->getSubType()->getStructuredSyntax();
		}

		if ($parts->match(MediaTypeInterface::PART_PARAMETERS) &&
			$mediaType->getParameters()->count())
		{
			if (\strlen($text))
				$text .= '; ';
			$text .= ParameterMapSerializer::serializeParameters(
				$mediaType->getParameters());
		}

		return $text;
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

	public function toString($partFlags = MediaTypeInterface::PART_ALL)
	{
		return self::serializeMediaTypeInterfaceToString($this,
			$partFlags);
	}

	#[\ReturnTypeWillChange]
	public function __serialize()
	{
		return $this->toString();
	}

	#[\ReturnTypeWillChange]
	public function __unserialize($serialized)
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
