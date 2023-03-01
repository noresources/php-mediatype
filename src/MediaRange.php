<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package MediaType
 */
namespace NoreSources\MediaType;

use NoreSources\NotComparableException;
use NoreSources\Container\Container;
use NoreSources\MediaType\Traits\MediaTypeCompareTrait;
use NoreSources\MediaType\Traits\MediaTypeParameterMapTrait;
use NoreSources\MediaType\Traits\MediaTypeSerializableTrait;
use NoreSources\MediaType\Traits\MediaTypeStructuredTextTrait;
use NoreSources\Type\TypeConversion;
use NoreSources\Type\TypeDescription;

class MediaRange implements MediaTypeInterface
{
	use MediaTypeStructuredTextTrait;
	use MediaTypeParameterMapTrait;
	use MediaTypeSerializableTrait;
	use MediaTypeCompareTrait;

	const ANY = '*';

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

	public function __clone()
	{
		$this->subType = clone $this->subType;
		$this->setParameters($this->getParameters());
	}

	public function match($b)
	{
		/**
		 *
		 * @var MediaTypeInterface $a
		 * @var MediaTypeInterface $b
		 */
		$a = $this;

		if (!($b instanceof MediaTypeInterface))
		{
			if (!TypeDescription::hasStringRepresentation($b))
				throw new NotComparableException($a, $b);

			$b = MediaRange::createFromString(TypeConversion::toString($b));
		}

		if ($b->getType() == MediaRange::ANY)
			return true;

		if ($a->getType() == MediaRange::ANY)
			return false;

		if (\strcasecmp($a->getType(), $b->getType()) != 0)
			return false;

		$ast = \strval(\implode('.', $a->getSubType()->getFacets()));
		$bst = \strval(\implode('.', $b->getSubType()->getFacets()));

		if ($bst == MediaRange::ANY)
			return true;
		if ($ast == MediaRange::ANY)
			return false;

		$stc = \strcasecmp($ast, $bst);

		if ($a->getSubType()->compare($b->getSubType()) >= 0 &&
			($a->getSubType()->getFacetCount() ==
			$b->getSubType()->getFacetCount()) && ($stc != 0))
			return false;

		if ($a->getSubType()->getFacetCount() >
			$b->getSubType()->getFacetCount())
			$stc = 0;

		$as = $a->getSubType()->getStructuredSyntax();
		$bs = $b->getSubType()->getStructuredSyntax();

		if (empty($as))
			return (empty($bs) && ($stc == 0));
		elseif (empty($bs))
			return false;

		return ($stc == 0) && (\strcasecmp($as, $bs) == 0);
	}

	/**
	 *
	 * @param string $mediaTypeString
	 *        	Media range string
	 * @throws MediaTypeException
	 * @return \NoreSources\MediaType\MediaRange
	 */
	public static function createFromString($mediaTypeString,
		$strict = true)
	{
		$pattern = RFC6838::MEDIA_RANGE_PATTERN;
		if ($strict)
			$pattern = '^' . $pattern . '$';
		else
			$pattern = '^[\x9\x20]*' . $pattern;

		$matches = [];
		if (!\preg_match(chr(1) . $pattern . chr(1) . 'i',
			$mediaTypeString, $matches))
			throw new MediaTypeException($mediaTypeString,
				'Not a valid media range string');

		$subType = new MediaSubType([
			self::ANY
		], null);
		if (Container::keyExists($matches, 2) && $matches[2] != self::ANY)
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

		return new MediaRange(
			Container::keyValue($matches, 1, self::ANY), $subType);
	}

	/**
	 *
	 * @deprecated Use createFromString()
	 */
	public static function fromString($mediaTypeString, $strict = true)
	{
		return self::createFromString($mediaTypeString, $strict);
	}

	/**
	 *
	 * @param MediaTypeInterface $a
	 * @param MediaTypeInterface $b
	 * @return number -1 if $a < $b, 1 if $a > $b, 0 if equal or not comparable
	 */
	public static function compareMediaRanges(MediaTypeInterface $a,
		MediaTypeInterface $b)
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