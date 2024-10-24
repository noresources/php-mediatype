<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package MediaType
 */
namespace NoreSources\MediaType\Traits;

use NoreSources\NotComparableException;
use NoreSources\MediaType\Comparison;
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
	public function precisionCompare($b)
	{
		$a = $this;

		if (!($b instanceof MediaTypeInterface))
		{
			if (!TypeDescription::hasStringRepresentation($b))
				throw new NotComparableException($a, $b);

			$b = MediaRange::createFromString(
				TypeConversion::toString($b));
		}

		return Comparison::rangePrecision($a, $b);
	}
}
