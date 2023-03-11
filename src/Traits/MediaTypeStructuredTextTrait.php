<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package MediaType
 */
namespace NoreSources\MediaType\Traits;

use NoreSources\MediaType\MediaRange;
use NoreSources\MediaType\MediaSubType;
use NoreSources\MediaType\StructuredSyntaxSuffixRegistry;

trait MediaTypeStructuredTextTrait
{

	/**
	 *
	 * @param boolean $registeredOnly
	 *        	Return type only if it is a registered type
	 * @return string|NULL Structured syntax type if any. NULL otherwise
	 */
	public function getStructuredSyntax($registeredOnly = false)
	{
		if (!($this->getSubType() instanceof MediaSubType))
			return null;

		$s = $this->getSubType()->getStructuredSyntax();
		if (!empty($s))
			return $s;

		if ($this->getSubType()->getFacetCount() == 1)
		{
			$facet = $this->getSubType()->getFacet(0);
			if (\strtolower($this->getType()) == 'text')
			{
				if ($registeredOnly &&
					!StructuredSyntaxSuffixRegistry::getInstance()->isRegistered(
						$facet))
					return null;

				if ($facet != MediaRange::ANY)
					return $facet;
			}

			/*
			 * Other types such as application/json
			 */
			if (StructuredSyntaxSuffixRegistry::getInstance()->isRegistered(
				$facet))
				return $facet;
		}

		return null;
	}
}