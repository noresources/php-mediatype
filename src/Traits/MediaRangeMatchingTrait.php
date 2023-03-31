<?php
namespace NoreSources\MediaType\Traits;

use NoreSources\NotComparableException;
use NoreSources\MediaType\MediaRange;
use NoreSources\MediaType\MediaTypeInterface;
use NoreSources\Type\TypeConversion;
use NoreSources\Type\TypeDescription;

/**
 * Default implementation of the MediaTypeInterface::match() method
 */
trait MediaRangeMatchingTrait
{

	public static function isMediaTypeMatchMediaRange($mediaType,
		$mediaRange)
	{
		/**
		 *
		 * @var MediaTypeInterface $mediaRange
		 * @var MediaTypeInterface $mediaType
		 */
		if (!($mediaType instanceof MediaTypeInterface))
		{
			if (!TypeDescription::hasStringRepresentation($mediaType))
				throw new NotComparableException($mediaRange, $mediaType);

			$mediaType = MediaRange::createFromString(
				TypeConversion::toString($mediaType));
		}

		if (!($mediaRange instanceof MediaTypeInterface))
		{
			if (!TypeDescription::hasStringRepresentation($mediaRange))
				throw new NotComparableException($mediaRange, $mediaType);

			$mediaRange = MediaRange::createFromString(
				TypeConversion::toString($mediaRange), true);
		}

		if ($mediaRange->getType() == MediaRange::ANY)
			return true;

		if (\strcasecmp($mediaType->getType(), $mediaRange->getType()) !=
			0)
			return false;

		$ast = \strval(
			\implode('.', $mediaType->getSubType()->getFacets()));
		$bst = \strval(
			\implode('.', $mediaRange->getSubType()->getFacets()));

		if ($bst == MediaRange::ANY)
			return true;

		$c = 0;
		try
		{
			$c = $mediaType->getSubType()->compare($mediaRange);
		}
		catch (NotComparableException $e)
		{
			return false;
		}

		if ($c < 0)
			return false;
		return self::matchStructuredSyntax(
			$mediaType->getSubType()->getStructuredSyntax(),
			$mediaRange->getSubType()->getStructuredSyntax());
	}
}