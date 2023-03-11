<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package MediaType
 */
namespace NoreSources\MediaType;

use NoreSources\NotComparableException;
use NoreSources\MediaType\Traits\MediaTypeCompareTrait;
use NoreSources\MediaType\Traits\MediaTypeMatchingTrait;
use NoreSources\MediaType\Traits\MediaTypeParameterMapTrait;
use NoreSources\MediaType\Traits\MediaTypeSerializableTrait;
use NoreSources\MediaType\Traits\MediaTypeStructuredTextTrait;
use NoreSources\Type\TypeConversion;
use NoreSources\Type\TypeDescription;

/**
 *
 * @see https://www.iana.org/assignments/media-types/media-types.xhtml
 *
 */
class MediaType implements MediaTypeInterface
{

	use MediaTypeStructuredTextTrait;
	use MediaTypeParameterMapTrait;
	use MediaTypeSerializableTrait;
	use MediaTypeCompareTrait;
	use MediaTypeMatchingTrait;

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
	 * @return \NoreSources\MediaType\MediaSubType
	 */
	public function getSubType()
	{
		return $this->subType;
	}

	/**
	 *
	 * @param string $type
	 *        	Main type
	 * @param MediaSubType $subType
	 *        	Sub type
	 */
	public function __construct($type, MediaSubType $subType = null)
	{
		$this->mainType = $type;
		$this->subType = $subType;
	}

	/**
	 * Short string representation
	 *
	 * @return string main type/sub type[+syntax] WITHOUT parameters
	 */
	public function __toString()
	{
		return strval($this->mainType) . '/' . strval($this->subType);
	}

	public function __clone()
	{
		$this->subType = clone $this->subType;
		$this->setParameters($this->getParameters());
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \NoreSources\MediaType\MediaTypeInterface::match()
	 */
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

			$b = MediaRange::createFromString(
				TypeConversion::toString($b));
		}

		if ($b->getType() == MediaRange::ANY)
			return true;

		if (\strcasecmp($a->getType(), $b->getType()) != 0)
			return false;

		$ast = \strval(\implode('.', $a->getSubType()->getFacets()));
		$bst = \strval(\implode('.', $b->getSubType()->getFacets()));

		if ($bst == MediaRange::ANY)
			return true;

		$c = 0;
		try
		{
			$c = $a->getSubType()->compare($b);
		}
		catch (NotComparableException $e)
		{
			return false;
		}

		if ($c < 0)
			return false;
		return self::matchStructuredSyntax(
			$a->getSubType()->getStructuredSyntax(),
			$b->getSubType()->getStructuredSyntax());
	}

	/**
	 *
	 * @param string $mediaTypeString
	 *        	Media type string
	 * @param boolean $withParameters
	 *        	Also parse parameters
	 * @return MediaType
	 */
	public static function createFromString($mediaTypeString,
		$withParameters = false)
	{
		return self::unserializeMediaTypeInterfaceInterface(
			static::class, RFC6838::MEDIA_TYPE_PATTERN, $mediaTypeString,
			$withParameters);
	}

	/**
	 * Media main type
	 *
	 * @var string
	 */
	private $mainType;

	/**
	 *
	 * @var MediaSubType
	 */
	private $subType;
}