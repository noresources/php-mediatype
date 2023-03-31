<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package MediaType
 */
namespace NoreSources\MediaType\Test;

use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\MediaType\MediaTypeMatcher;

final class MediaTypeMatcherTest extends \PHPUnit\Framework\TestCase
{

	public function testMatcher()
	{
		$tests = [
			'easy' => [
				'mediaType' => 'application/json',
				'list' => [
					'text/plain',
					'application/json'
				],
				'matches' => [
					'application/json'
				]
			],
			'fine grained' => [
				'mediaType' => 'text/special.property',
				'list' => [
					'text/*',
					'text/special.property',
					'text/special.property.precision',
					'image/special.property',
					'text/special'
				],
				'matches' => [
					'text/*',
					'text/special.property',
					'text/special'
				]
			]
		];

		foreach ($tests as $label => $test)
		{
			$mediaType = $test['mediaType'];
			$list = $test['list'];
			$matches = $test['matches'];

			$matcher = new MediaTypeMatcher(
				MediaTypeFactory::getInstance()->createFromString(
					$mediaType));
			$actual = $matcher->getMatching($list);
			$this->assertEquals($matches, $actual, $label . ' matches');
		}
	}
}
