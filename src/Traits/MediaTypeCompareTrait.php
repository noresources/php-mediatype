<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package MediaType
 */
namespace NoreSources\MediaType\Traits;

use NoreSources\NotComparableException;
use NoreSources\MediaType\MediaRange;
use NoreSources\MediaType\MediaSubType;
use NoreSources\MediaType\MediaTypeInterface;
use NoreSources\Type\TypeConversion;
use NoreSources\Type\TypeDescription;

trait MediaTypeCompareTrait
{

	/**
	 * Compare media range precision
	 *
	 * @param MediaTypeInterface|MediaSubType|string $b
	 *        	Media range to compare
	 * @throws NotComparableException
	 * @return 0 if media range are identical,
	 *         < 0 if $b is more precise,
	 *         > 0 if $b is less precise
	 */
	public function compare($b)
	{
		$a = $this;

		if (!($b instanceof MediaTypeInterface))
		{
			if (!TypeDescription::hasStringRepresentation($b))
				throw new NotComparableException($a, $b);

			$b = MediaRange::createFromString(
				TypeConversion::toString($b));
		}

		if ($a->getType() == MediaRange::ANY)
			return (($b->getType() == MediaRange::ANY) ? 0 : -1);
		elseif ($b->getType() == MediaRange::ANY)
			return 1;

		if (\strcasecmp($a->getType(), $b->getType()) !== 0)
			throw new NotComparableException($a->getType(),
				$b->getType());

		if ($a->getSubType() == MediaRange::ANY)
			return (($b->getSubType() == MediaRange::ANY) ? 0 : -1);
		elseif ($b->getSubType() == MediaRange::ANY)
			return 1;

		return $a->getSubType()->compare($b->getSubType());
	}
}
