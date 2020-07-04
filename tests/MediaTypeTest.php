<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 *
 * @package Core
 */
namespace NoreSources;

use NoreSources\Http\ParameterMap;
use NoreSources\MediaType\MediaRange;
use NoreSources\MediaType\MediaSubType;
use NoreSources\MediaType\MediaType;
use NoreSources\MediaType\MediaTypeException;
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\MediaType\MediaTypeInterface;

final class MediaTypeTest extends \PHPUnit\Framework\TestCase
{

	public function testParse()
	{
		$tests = [
			'text/html' => [
				'valid' => true,
				'class' => MediaType::class,
				'type' => 'text',
				'subtype' => [
					'text' => 'html',
					'facets' => [
						'html'
					],
					'syntax' => null
				],
				'syntax' => 'html'
			],
			'text/*' => [
				'valid' => true,
				'class' => MediaRange::class,
				'type' => 'text',
				'subtype' => null,
				'syntax' => null
			],
			'*/*' => [
				'valid' => true,
				'class' => MediaRange::class,
				'type' => '*',
				'subtype' => null,
				'syntax' => null
			],
			'text/vnd.abc' => [
				'valid' => true,
				'class' => MediaType::class,
				'type' => 'text',
				'subtype' => [
					'text' => 'vnd.abc',
					'facets' => [
						'vnd',
						'abc'
					],
					'syntax' => null
				],
				'syntax' => null
			],
			'image/vnd.noresources.amazing.format' => [
				'valid' => true,
				'class' => MediaType::class,
				'type' => 'image',
				'subtype' => [
					'text' => 'vnd.noresources.amazing.format',
					'facets' => [
						'vnd',
						'noresources',
						"amazing",
						'format'
					],
					'syntax' => null
				],
				'syntax' => null
			],
			'text/vnd.noresources.incredibly.flexible+xml' => [
				'valid' => true,
				'class' => MediaType::class,
				'type' => 'text',
				'subtype' => [
					'text' => 'vnd.noresources.incredibly.flexible+xml',
					'facets' => [
						'vnd',
						'noresources',
						"incredibly",
						'flexible'
					],
					'syntax' => 'xml'
				],
				'syntax' => 'xml'
			],
			'application/alto-costmap+json' => [
				'valid' => true,
				'class' => MediaType::class,
				'type' => 'application',
				'subtype' => [
					'text' => 'alto-costmap+json',
					'facets' => [
						'alto-costmap'
					],
					'syntax' => 'json'
				],
				'syntax' => 'json'
			]
		];

		foreach ($tests as $text => $parsed)
		{
			$mediaType = null;
			try
			{
				$mediaType = MediaTypeFactory::fromString($text,
					$parsed['class'] == MediaRange::class);
			}
			catch (MediaTypeException $e)
			{
				if ($parsed['valid'])
					throw $e;
				continue;
			}

			$this->assertInstanceOf($parsed['class'], $mediaType, $text);
			$this->assertEquals($parsed['type'], $mediaType->getType(), $text . ' name');

			if ($parsed['subtype'])
			{
				$this->assertInstanceOf(MediaSubType::class, $mediaType->getSubType(),
					$text . ' subtype');

				$subType = $mediaType->getSubType();

				$this->assertCount(count($parsed['subtype']['facets']), $subType->getFacets(),
					$text . ' subtype facets');

				$this->assertEquals($parsed['subtype']['syntax'], $subType->getStructuredSyntax(),
					$text . ' subtype syntax');

				foreach ($parsed['subtype']['facets'] as $index => $facet)
				{
					$this->assertEquals($facet, $subType->getFacet($index),
						$text . ' subtype facet ' . $index);
				}
			}
			else
				$this->assertEquals(MediaRange::ANY, $mediaType->getSubType(), 'Subtype is a range');

			$this->assertEquals($parsed['syntax'], $mediaType->getStructuredSyntax(),
				$text . ' syntax');

			$this->assertEquals($text, strval($mediaType), $text . ' to string');
		}
	}

	public function testFromMedia()
	{
		$this->assertEquals('application/json',
			strval(MediaTypeFactory::fromMedia(__DIR__ . '/data/a.json')));
	}

	public function testCompareSubTypes()
	{
		$tests = [
			'identical' => [
				'html',
				'html',
				0
			],
			'identical (3 facets)' => [
				'vnc.ns.php',
				'vnc.ns.php',
				0
			],
			'not comparable' => [
				'html',
				'json',
				0
			],
			'more precise' => [
				'vnd.ns.php',
				'vnd.ns',
				1
			],
			'less precise' => [
				'vnd.ns',
				'vnd.ns.php',
				-1
			]
		];

		foreach ($tests as $test)
		{
			$a = null;
			$b = null;
			try
			{
				$a = MediaRange::fromString('whatever/' . $test[0]);
				$a = $a->getSubType();
				$b = MediaRange::fromString('whatever/' . $test[1]);
				$b = $b->getSubType();

				$result = $a->compare($b);
			}
			catch (\Exception $e)
			{
				$this->assertEquals('No error', $e->getMessage(), 'MediaType creation');
				continue;
			}

			$this->assertEquals($test[2], $result, $test[0] . ' < ' . $test[1] . ' = ...');
		}
	}

	public function testCompareRanges()
	{
		$tests = [
			'identical types' => [
				'text/html',
				'text/html',
				0
			],
			'identical ranges' => [
				'text/*',
				'text/*',
				0
			],
			'identical ranges (any)' => [
				'*/*',
				'*/*',
				0
			],
			'not comparable' => [
				'text/html',
				'font/ttf',
				0
			],
			'type more precise than any' => [
				'text/*',
				'*/*',
				1
			],
			'subtype more precise than any' => [
				'text/xml',
				'text/*',
				1
			],
			'more precise subtype' => [
				'application/vnd.ns.php',
				'application/vnd.ns',
				1
			]
		];

		foreach ($tests as $test)
		{
			$a = MediaTypeFactory::fromString($test[0], true);
			$b = MediaTypeFactory::fromString($test[1], true);

			$label = $test[0] . ' < ' . $test[1];

			$this->assertInstanceOf(MediaTypeInterface::class, $a, $label . ' left operand class');
			$this->assertInstanceOf(MediaTypeInterface::class, $b, $label . ' right operand class');

			$result = MediaRange::compare($a, $b);

			$this->assertEquals($test[2], $result, $label . ' = ...');
		}
	}

	public function testSerialize()
	{
		$tests = [
			'basic' => [
				'serialized' => 'text/plain',
				'type' => 'text',
				'subtype' => 'plain',
				'parameters' => []
			],
			'with params' => [
				'serialized' => 'text/javascript; charset=utf-8',
				'type' => 'text',
				'subtype' => 'javascript',
				'parameters' => [
					'charset' => 'utf-8'
				]
			],
			'range with params' => [
				'class' => MediaRange::class,
				'serialized' => 'text/*; charset=utf-8; foo="bar baz"',
				'type' => 'text',
				'subtype' => '*',
				'parameters' => [
					'charset' => 'utf-8',
					'foo' => 'bar baz'
				]
			]
		];

		foreach ($tests as $label => $test)
		{
			$className = Container::keyValue($test, 'class', MediaType::class);
			$cls = new \ReflectionClass($className);
			$mt = $cls->newInstanceArgs([
				null,
				null
			]);
			$mt->unserialize($test['serialized']);

			$this->assertEquals($test['type'], $mt->getType(), 'Type');
			$this->assertEquals($test['subtype'], \strval($mt->getSubType()), 'SubType');
			$this->assertEquals($test['parameters'], $mt->getParameters()
				->getArrayCopy(), 'Parameters');

			$serialized = $mt->serialize();
		}
	}

	public function testParameters()
	{
		$m = MediaType::fromString('text/plain');
		$p = $m->getParameters();

		$this->assertInstanceOf(ParameterMap::class, $p);

		$p->offsetSet('Charset', 'utf-8');

		$this->assertCount(1, $p, 'Parameter count');
		$this->assertEquals('utf-8', $p['Charset'], 'Strict case getParameter');
		$this->assertTrue($p->offsetExists('charset'), 'Case-insensitive offsetExists');
		$this->assertEquals('utf-8', $p['charset'], 'Case-insensitive getParameter');
	}
}
