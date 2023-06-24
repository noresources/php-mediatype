<?php
namespace NoreSources\MediaType;

use NoreSources\SingletonTrait;
use NoreSources\MediaType\Traits\MediaRangeMatchingTrait;
use NoreSources\Type\TypeDescription;

/**
 * Provides informations about media type/range.
 */
class MediaTypeInspector
{
	use SingletonTrait;
	use MediaRangeMatchingTrait;

	/**
	 *
	 * @param MediaTypeInterface|string $input
	 *        	Media type/range
	 * @throws \InvalidArgumentException
	 * @return boolean TRUE if input type or subtype value is the "*" wildcard character.
	 */
	public function hasWildcard($input)
	{
		if ($input instanceof MediaTypeInterface)
			return ($input->getType() == MediaRange::ANY ||
				\strval($input->getSubType()) == MediaRange::ANY);

		if (!\is_string($input))
			throw new \InvalidArgumentException(
				MediaTypeInterface::class . ' or string expected. Got ' .
				TypeDescription::getName($input));

		if (\substr($input, 0, 2) == '*/')
			return true;

		return \preg_match(
			chr(1) . '^' . RFC6838::RESTRICTED_NAME_PATTERN . '/\*' .
			chr(1), $input) == 1;
	}

	/**
	 *
	 * @param MediaTypeInterface|string $input
	 *        	Media type or range
	 * @return boolean TRUE if both media type and subtype are valid RFC6838 restricted names.
	 */
	public function isFullySpecifiedMediaType($input)
	{
		return !$this->hasWildcard($input);
	}

	/**
	 *
	 * @param MediaTypeInterface|string $mediaType
	 *        	Media type
	 * @param MediaTypeInterface|string $mediaRange
	 *        	Media range
	 * @return boolean TRUE if $mediaType is compatible with $mediaRange
	 */
	public function matchMediaRange($mediaType, $mediaRange)
	{
		return self::isMediaTypeMatchMediaRange($mediaType, $mediaRange);
	}
}
