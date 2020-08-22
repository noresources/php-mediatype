<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources\MediaType;

use NoreSources\Container;

class MediaRange implements MediaTypeInterface
{
	use MediaTypeStructuredTextTrait;
	use MediaTypeParameterMapTrait;
	use MediaTypeSerializableTrait;
	use MediaTypeCompareTrait;

	const ANY = '*';

	const STRING_PATTERN = '(?:\*/\*)|(?:([a-z0-9](?:[a-z0-9!#$&^_-]{0,126}))/(?:(?:\*)|((?:[a-z0-9](?:[a-z0-9!#$&^_-]{0,126}))(?:\.(?:[a-z0-9](?:[a-z0-9!#$&^_-]{0,126})))*)(?:\+([a-z0-9](?:[a-z0-9!#$&^_-]{0,126})))*))';

	public function __construct($type = self::ANY, $subType = self::ANY)
	{
		$this->mainType = $type;
		$this->subType = $subType;
	}

	/**
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->mainType;
	}

	/**
	 *
	 * @return MediaSubType|string
	 */
	public function getSubType()
	{
		return $this->subType;
	}

	public function __toString()
	{
		return $this->mainType . '/' . strval($this->subType);
	}

	/**
	 *
	 * @param string $mediaTypeString
	 *        	Media range string
	 * @throws MediaTypeException
	 * @return \NoreSources\MediaType\MediaRange
	 */
	public static function fromString($mediaTypeString, $strict = true)
	{
		$pattern = self::STRING_PATTERN;
		if ($strict)
			$pattern = '^' . $pattern . '$';
		else
			$pattern = '^[\x9\x20]*' . $pattern;

		$matches = [];
		if (!\preg_match(chr(1) . $pattern . chr(1) . 'i', $mediaTypeString, $matches))
			throw new MediaTypeException($mediaTypeString, 'Not a valid media range string');

		$subType = self::ANY;
		if (Container::keyExists($matches, 2))
		{
			$facets = explode('.', $matches[2]);
			$syntax = Container::keyValue($matches, 3, null);
			$subType = new MediaSubType($facets, $syntax);
		}

		return new MediaRange(Container::keyValue($matches, 1, self::ANY), $subType);
	}

	/**
	 *
	 * @param MediaTypeInterface $a
	 * @param MediaTypeInterface $b
	 * @return number -1 if $a < $b, 1 if $a > $b, 0 if equal or not comparable
	 */
	public static function compareMediaRanges(MediaTypeInterface $a, MediaTypeInterface $b)
	{
		return $a->compare($b);
	}

	/**
	 *
	 * @var \NoreSources\MediaType\MediaType|"*"
	 */
	private $mainType;

	/**
	 *
	 * @var \NoreSources\MediaType\MediaSubType|"*"
	 */
	private $subType;
}