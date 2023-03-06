<?php
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\MediaType\MediaTypeFileExtensionRegistry;
use NoreSources\MediaType\MediaTypeInterface;

class MediaTypeFileExtensionRegistryTest extends \PHPUnit\Framework\TestCase
{

	public function testBasic()
	{
		$tests = [
			'json' => [
				'type' => 'application/json',
				"extension" => 'json'
			]
		];

		$registry = MediaTypeFileExtensionRegistry::getInstance();

		foreach ($tests as $label => $test)
		{
			$mt = MediaTypeFactory::createFromString($test['type']);
			$extension = $test['extension'];
			$extensions = $registry->getMediaTypeExtensions($mt);
			$this->assertEquals('array', gettype($extensions),
				$label . ' has associated extensions');
			$this->assertContains($extension, $extensions,
				$test['type'] . ' has ' . $extension . ' extension');

			$mt2 = $registry->getExtensionMediaType($extension);
			$this->assertInstanceOf(MediaTypeInterface::class, $mt2,
				$label . ' media type found by extension');
			$this->assertEquals(\strval($mt), \strval($mt2));
		}
	}
}
