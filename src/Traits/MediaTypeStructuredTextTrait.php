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
use NoreSources\MediaType\MediaTypeInterface;
use NoreSources\MediaType\StructuredSyntaxSuffixRegistry;

trait MediaTypeStructuredTextTrait
{

	public function getStructuredSyntax($toleranceFlags = 0)
	{
		$registeredOnly = false;
		if (\is_bool($toleranceFlags))
		{
			$registeredOnly = $toleranceFlags;
			$toleranceFlags = ($toleranceFlags) ? MediaTypeInterface::STRUCTURED_TEXT_ONLY_REGISTERED : 0;
		}
		$bypassTreePrefix = ($toleranceFlags &
			MediaTypeInterface::STRUCTURED_TEXT_BYPASS_KNOWN_TREE_FACET) ==
			MediaTypeInterface::STRUCTURED_TEXT_BYPASS_KNOWN_TREE_FACET;
		$st = $this->getSubType();

		if (!($st instanceof MediaSubType))
			return null;

		$s = $st->getStructuredSyntax();
		if (!empty($s))
			return $s;

		$c = $st->getFacetCount();
		if ($c == 0)
			return null;

		if ($c == 1)
			$facet = $st->getFacet(0);
		elseif ($c == 2 && !$registeredOnly && $bypassTreePrefix &&
			\in_array($st->getFacet(0),
				[
					MediaSubType::FACET_PERSONAL,
					MediaSubType::FACET_UNREGISTERED,
					MediaSubType::FACET_VENDOR
				]))
			$facet = $st->getFacet(1);
		else
			return null;

		if ($registeredOnly &&
			!StructuredSyntaxSuffixRegistry::getInstance()->isRegistered(
				$facet))
			return null;

		if ($facet == MediaRange::ANY)
			return null;

		if ((($toleranceFlags &
			MediaTypeInterface::STRUCTURED_TEXT_REMOVE_LEGACY_UNREGISTERED_PREFIX) ==
			MediaTypeInterface::STRUCTURED_TEXT_REMOVE_LEGACY_UNREGISTERED_PREFIX) &&
			\strcasecmp('x-', \substr($facet, 0, 2)) == 0)
			return \substr($facet, 2);

		return $facet;
	}
}