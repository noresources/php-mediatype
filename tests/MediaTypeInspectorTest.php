<?php
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\MediaType\MediaTypeInspector;
use NoreSources\Type\TypeDescription;

class MediaTypeInspectorTest extends \PHPUnit\Framework\TestCase
{

	public function testWildcard()
	{
		$tests = [
			'application/json' => false,
			'text/*' => true,
			'foo/bar; wildcard=*' => false,
			'*/*' => true
		];
		$inspector = MediaTypeInspector::getInstance();
		foreach ($tests as $test => $expected)
		{
			foreach ([
				$test,
				MediaTypeFactory::getInstance()->createFromString($test,
					true)
			] as $input)
			{
				$actual = $inspector->hasWildcard($input);
				$this->assertEquals($expected, $actual,
					TypeDescription::getLocalName($input) . ' ' . $input .
					($expected ? '' : ' does not') . ' have wildcard(s)');
				$actual = $inspector->isFullySpecifiedMediaType($input);
				$this->assertEquals(!$expected, $actual,
					TypeDescription::getLocalName($input) . ' ' . $input .
					($expected ? ' is not' : 'is') . ' fully specified');
			}
		}
	}
}
