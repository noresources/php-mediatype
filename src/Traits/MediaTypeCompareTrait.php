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
use NoreSources\MediaType\MediaTypeInterface;
use NoreSources\Type\TypeConversion;
use NoreSources\Type\TypeDescription;

trait MediaTypeCompareTrait
{

	public function compare($b)
	{
		$a = $this;

		if (!($b instanceof MediaTypeInterface))
		{
			if (!TypeDescription::hasStringRepresentation($b))
				throw new NotComparableException($a, $b);

			$b = MediaRange::createFromString(TypeConversion::toString($b));
		}

		if ($a->getType() == MediaRange::ANY)
			return (($b->getType() == MediaRange::ANY) ? 0 : -1);
		elseif ($b->getType() == MediaRange::ANY)
			return 1;

		if ($a->getSubType() == MediaRange::ANY)
			return (($b->getSubType() == MediaRange::ANY) ? 0 : -1);
		elseif ($b->getSubType() == MediaRange::ANY)
			return 1;

		return $a->getSubType()->compare($b->getSubType());
	}
}
