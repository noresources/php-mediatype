<?php
namespace NoreSources\MediaType\Traits;

trait MediaTypeMatchingTrait
{

	public static function matchStructuredSyntax($a, $b)
	{
		if (empty($a))
			return empty($b);
		if (empty($b))
			return true;
		return \strcasecmp($a, $b) === 0;
	}
}
