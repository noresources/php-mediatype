<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package MediaType
 */
namespace NoreSources\MediaType\Test;

use NoreSources\NotComparableException;
use NoreSources\Container\Container;
use NoreSources\Http\ParameterMap;
use NoreSources\MediaType\MediaRange;
use NoreSources\MediaType\MediaSubType;
use NoreSources\MediaType\MediaType;
use NoreSources\MediaType\MediaTypeException;
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\MediaType\MediaTypeInterface;
use NoreSources\MediaType\MediaTypeRegistry;
use NoreSources\Type\TypeDescription;

class MediaTypeTest extends \PHPUnit\Framework\TestCase
{

	public function __construct($name = null, $data = array(),
		$dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		$this->streams = [];
	}

	public function __destruct()
	{
		foreach ($this->streams as $value)
		{
			\fclose($value);
		}
	}

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
			'text/x-c++' => [
				'valid' => true,
				'class' => MediaType::class,
				'type' => 'text',
				'subtype' => [
					'text' => 'x-c++',
					'facets' => [
						'x-c++'
					],
					'syntax' => null
				],
				'syntax' => 'x-c++'
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
			],
			'text/html; charset=utf-8' => [
				'valid' => true,
				'class' => MediaType::class,
				'string' => 'text/html',
				'type' => 'text',
				'subtype' => [
					'text' => 'html',
					'facets' => [
						'html'
					]
				],
				'parameters' => [
					'charset' => 'utf-8'
				],
				'syntax' => 'html'
			]
		];

		foreach ($tests as $text => $parsed)
		{
			$mediaType = null;
			try
			{
				$mediaType = MediaTypeFactory::getInstance()->createFromString(
					$text, $parsed['class'] == MediaRange::class);
			}
			catch (MediaTypeException $e)
			{
				if ($parsed['valid'])
					throw $e;
				continue;
			}

			$this->assertInstanceOf($parsed['class'], $mediaType, $text);
			$this->assertEquals($parsed['type'], $mediaType->getType(),
				$text . ' name');

			$this->assertEquals(
				Container::keyValue($parsed, 'string', $text),
				\strval($mediaType), $text . ' to string');

			$this->assertEquals(
				Container::keyValue($parsed, 'serialized', $text),
				$mediaType->jsonSerialize(), $text . ' (re-)serialized');

			if ($parsed['subtype'])
			{
				$this->assertInstanceOf(MediaSubType::class,
					$mediaType->getSubType(), $text . ' subtype');

				$subType = $mediaType->getSubType();

				$this->assertCount(count($parsed['subtype']['facets']),
					$subType->getFacets(), $text . ' subtype facets');

				$this->assertEquals(
					Container::keyValue($parsed['subtype'], 'syntax'),
					$subType->getStructuredSyntax(),
					$text . ' subtype syntax');

				foreach ($parsed['subtype']['facets'] as $index => $facet)
				{
					$this->assertEquals($facet,
						$subType->getFacet($index),
						$text . ' subtype facet ' . $index);
				}
			}
			else
				$this->assertEquals(MediaRange::ANY,
					$mediaType->getSubType(), 'Subtype is a range');

			$this->assertEquals(Container::keyValue($parsed, 'syntax'),
				$mediaType->getStructuredSyntax(), $text . ' syntax');
		}
	}

	public function testFromMedia()
	{
		$tests = [
			'Existing JSON file' => [
				'media' => __DIR__ . '/data/a.json',
				'expected' => 'application/json'
			],
			'C++ source in a .js file' => [
				'media' => __DIR__ . '/data/c++.js',
				'expected' => 'text/x-c++'
			],
			'Empty XML file' => [
				'media' => __DIR__ . '/data/empty.xml',
				'expected' => 'application/xml'
			],
			'nada' => [
				'media' => 'Skarabutcha !',
				'expected' => MediaTypeException::class
			],
			'data:// stream' => [
				'media' => $this->addStream(
					\fopen('data://text/csv,foo,bar', 'r')),
				'expected' => 'text/csv'
			]
		];

		foreach ($tests as $label => $test)
		{
			$media = $test['media'];
			$expected = $test['expected'];
			$message = '';
			try
			{
				$mediaType = MediaTypeFactory::getInstance()->createFromMedia(
					$media, MediaTypeFactory::FROM_ALL);
			}
			catch (\Exception $e)
			{
				$message = $e->getMessage();
				$mediaType = TypeDescription::getName($e);
			}

			if (\strlen($message))
				$label .= ' ' . $message;
			$this->assertEquals($expected, \strval($mediaType), $label);
		}

		$mode = MediaTypeFactory::FROM_ALL |
			MediaTypeFactory::FROM_EXTENSION_FIRST;

		$this->assertStringEndsWith('/javascript',
			strval(
				MediaTypeFactory::getInstance()->createFromMedia(
					__DIR__ . '/data/c++.js', $mode)),
			'C++ code in a .js file (extension first)');
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
				NotComparableException::class
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

		foreach ($tests as $label => $test)
		{
			$a = null;
			$b = null;
			$actual = null;
			$expected = $test[2];
			$label = $test[0] . ' < ' . $test[1] . ' = ' . $expected .
				' (' . $label . ')';
			try
			{
				$a = MediaRange::createFromString(
					'whatever/' . $test[0]);
				$a = $a->getSubType();
				$b = MediaRange::createFromString(
					'whatever/' . $test[1]);
				$b = $b->getSubType();

				$actual = $a->compare($b);
			}
			catch (\Exception $e)
			{
				$actual = TypeDescription::getName($e);
			}

			$this->assertEquals($test[2], $actual, $label);
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
				NotComparableException::class
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
			],
			'media type  not comparable' => [
				'text/plain',
				'image/png',
				NotComparableException::class
			],
			'media range  not comparable' => [
				'text/*',
				'image/*',
				NotComparableException::class
			],
			'sub type not comparable' => [
				'image/jpg',
				'image/png',
				NotComparableException::class
			]
		];

		foreach ($tests as $test)
		{
			$a = MediaTypeFactory::getInstance()->createFromString(
				$test[0], true);
			$b = MediaTypeFactory::getInstance()->createFromString(
				$test[1], true);
			$expected = $test[2];

			$label = $test[0] . ' < ' . $test[1];

			$this->assertInstanceOf(MediaTypeInterface::class, $a,
				$label . ' left operand class');
			$this->assertInstanceOf(MediaTypeInterface::class, $b,
				$label . ' right operand class');

			$actual = null;
			try
			{
				$actual = MediaRange::compareMediaRanges($a, $b);
			}
			catch (\Exception $e)
			{
				$actual = TypeDescription::getName($e);
			}

			$this->assertEquals($expected, $actual,
				$label . ' = ' . $expected);

			if (!\is_integer($expected))
				continue;
			$expected = -$expected;
			$label = $test[1] . ' < ' . $test[0];
			try
			{
				$actual = MediaRange::compareMediaRanges($b, $a);
			}
			catch (\Exception $e)
			{
				$actual = TypeDescription::getName($e);
			}

			$this->assertEquals($expected, $actual,
				$label . ' = ' . $expected);
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
			$className = Container::keyValue($test, 'class',
				MediaType::class);
			$cls = new \ReflectionClass($className);
			$mt = $cls->newInstanceArgs([
				null,
				null
			]);
			$mt = \call_user_func([
				$className,
				'createFromString'
			], $test['serialized'], true);

			$this->assertEquals($test['type'], $mt->getType(), 'Type');
			$this->assertEquals($test['subtype'],
				\strval($mt->getSubType()), 'SubType');
			$this->assertEquals($test['parameters'],
				$mt->getParameters()
					->getArrayCopy(), 'Parameters');

			$serialized = $mt->jsonSerialize();
		}
	}

	public function testParameters()
	{
		$m = MediaType::createFromString('text/plain');
		$p = $m->getParameters();

		$this->assertInstanceOf(ParameterMap::class, $p);

		$p->offsetSet('Charset', 'utf-8');

		$this->assertCount(1, $p, 'Parameter count');
		$this->assertEquals('utf-8', $p['Charset'],
			'Strict case getParameter');
		$this->assertTrue($p->offsetExists('charset'),
			'Case-insensitive offsetExists');
		$this->assertEquals('utf-8', $p['charset'],
			'Case-insensitive getParameter');
	}

	public function testMatch()
	{
		$tests = [
			'Identical' => [
				'a' => 'text/plain',
				'b' => 'text/plain'
			],
			'Different' => [
				'a' => 'text/plain',
				'b' => 'text/empty',
				'expected' => false
			],
			'Wildcard' => [
				'a' => 'text/plain',
				'b' => '*/*'
			],
			'Sub type wildcard' => [
				'a' => 'text/plain',
				'b' => 'text/*'
			],
			'Sub type wildcard 2' => [
				'a' => 'image/png',
				'b' => 'text/*',
				'expected' => false
			],
			'More precise subtype' => [
				'a' => 'text/csv',
				'b' => 'text/csv.with.semicolon',
				'expected' => false
			],
			'Less precise subtype' => [
				'a' => 'text/csv.with.semicolon',
				'b' => 'text/csv',
				'expected' => true
			],
			'Identical with syntax' => [
				'a' => 'application/foo+json',
				'b' => 'application/foo+json',
				'expected' => true
			],
			'Without syntax' => [
				'a' => 'application/foo',
				'b' => 'application/foo+json',
				'expected' => false
			],
			'With syntax' => [
				'a' => 'application/foo+json',
				'b' => 'application/foo',
				'expected' => true
			],
			'Less restrictive sub type' => [
				'a' => 'text/a.b.c',
				'b' => 'text/a.b',
				'expected' => true
			],
			'More restrictive sub type' => [
				'a' => 'text/a.b',
				'b' => 'text/a.b.c',
				'expected' => false
			],
			'Range 1' => [
				'a' => 'foo/*',
				'b' => '*/*'
			],
			'Range 2' => [
				'a' => 'foo/*',
				'b' => 'foo/*'
			],
			'Range 3' => [
				'a' => '*/*',
				'b' => '*/*'
			],
			'Range 4' => [
				'a' => '*/*',
				'b' => 'foo/*',
				'expected' => false
			]
		];
		foreach ($tests as $label => $test)
		{
			$a = MediaTypeFactory::getInstance()->createFromString(
				$test['a']);
			$b = MediaTypeFactory::getInstance()->createFromString(
				$test['b']);
			$expected = Container::keyValue($test, 'expected', true);

			$actual = $a->match($b);
			$this->assertEquals($expected, $actual,
				$label . ' ' . \strval($a) . ' vs ' . \strval($b));
		}
	}

	public function testRegistry()
	{
		$registry = MediaTypeRegistry::getInstance();
		foreach ([
			'text/plain' => true,
			'foo/bar' => false,
			'application/json' => true
		] as $type => $registered)
		{
			$asString = $registry->isRegistered($type);
			$this->assertEquals($registered, $asString,
				$type . ' (string) is ' . ($registered ? 'not ' : '') .
				'registered');
			try
			{
				$type = MediaTypeFactory::getInstance()->createFromString(
					$type);
			}
			catch (\Exception $e)
			{}
			if ($type instanceof MediaTypeInterface)
			{
				$asMediaType = $registry->isRegistered($type);
				$this->assertEquals($registered, $asString,
					\strval($type) . ' (MediaType) is ' .
					($registered ? 'not ' : '') . 'registered');
			}
		}
	}

	public function testClone()
	{
		$a = MediaType::createFromString('text/plain');
		$sa = $a->jsonSerialize();

		$b = clone $a;
		$this->assertEquals($sa, $b->serializeToString(), 'Clone');

		$b->getParameters()->offsetSet('charset', 'utf-8');
		$sb = $b->serializeToString();

		$this->assertEquals($sa, $a->serializeToString(),
			'Clone modification does not affect original parameter map');
		$a->getParameters()->offsetSet('foo', 'bar');
		$this->assertEquals($sb, $b->serializeToString(),
			'Source modification does not affect clone parameter map');
	}

	private function addStream($stream)
	{
		$this->streams[] = $stream;
		return $stream;
	}
}







