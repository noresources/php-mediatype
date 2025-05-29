<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package MediaType
 */
namespace NoreSources\MediaType;

use NoreSources\MediaType\Traits\MediaRangeMatchingTrait;
use NoreSources\MediaType\Traits\MediaTypeCompareTrait;
use NoreSources\MediaType\Traits\MediaTypeParameterMapTrait;
use NoreSources\MediaType\Traits\MediaTypeSerializationTrait;
use NoreSources\MediaType\Traits\MediaTypeStructuredTextTrait;
use NoreSources\MediaType\Traits\StructuredSyntaxMatchingTrait;

/**
 *
 * @see https://www.iana.org/assignments/media-types/media-types.xhtml
 *
 */
class MediaType implements MediaTypeInterface
{

	use MediaTypeStructuredTextTrait;
	use MediaTypeParameterMapTrait;
	use MediaTypeSerializationTrait;
	use MediaRangeMatchingTrait;
	use MediaTypeCompareTrait;
	use StructuredSyntaxMatchingTrait;

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
	public function __construct($type, ?MediaSubType $subType = null)
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
	public function match($mediaRange)
	{
		return $this->isMediaTypeMatchMediaRange($this, $mediaRange);
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