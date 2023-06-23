<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package MediaType
 */
namespace NoreSources\MediaType\Test;

use NoreSources\MediaType\MediaRange;
use NoreSources\MediaType\MediaType;
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\MediaType\MediaTypeFactoryAwareInterface;
use NoreSources\MediaType\Traits\MediaTypeFactoryAwareTrait;

class Aware implements MediaTypeFactoryAwareInterface
{
	use MediaTypeFactoryAwareTrait;
}

class MediaTypeFactoryTest extends \PHPUnit\Framework\TestCase
{

	public function testAware()
	{
		$dflt = MediaTypeFactory::getInstance();
		$aware = new Aware();
		$this->assertEquals(NULL, $aware->getMediaTypeFactory(),
			'Aware factory is NULL by default');
		$aware->setMediaTypeFactory($dflt);
		$this->assertEquals($dflt, $aware->getMediaTypeFactory(),
			'Aware factory set');
	}

	public function testFromString()
	{
		$stringRepresentation = 'application/json; style=json; charset=utf-8';
		$mediaTypeStaticMethod = MediaType::createFromString(
			$stringRepresentation, true);

		$this->assertEquals($stringRepresentation,
			$mediaTypeStaticMethod->jsonSerialize(), 'Media type method');

		$mediaRangeStaticMethod = MediaRange::createFromString(
			$stringRepresentation, true);

		$this->assertEquals($stringRepresentation,
			$mediaRangeStaticMethod->jsonSerialize(),
			'Media range method');

		$factoryMethod = MediaTypeFactory::getInstance()->createFromString(
			$stringRepresentation, true);
		$this->assertEquals($stringRepresentation,
			$factoryMethod->jsonSerialize(), 'Factory method');

		foreach ([
			'media type vs media range' => [
				$mediaTypeStaticMethod,
				$mediaTypeStaticMethod
			],
			'media type vs factory' => [
				$mediaTypeStaticMethod,
				$factoryMethod
			],
			'factory vs media range' => [
				$factoryMethod,
				$mediaRangeStaticMethod
			]
		] as $label => $pair)
		{
			list ($actual, $expected) = $pair;

			$this->assertEquals($actual->jsonSerialize(),
				$expected->jsonSerialize(), $label);
		}
	}
}
