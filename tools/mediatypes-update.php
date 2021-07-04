<?php
namespace NoreSources\MediaType;

require (__DIR__ . '/../vendor/autoload.php');

use NoreSources\Type\TypeConversion;
$url = 'https://www.iana.org/assignments/media-types/media-types.xml';
$filename = __DIR__ . '/../resources/iana/media-types.xml';

$outputFileBase = __DIR__ . '/../src/MediaTypeRegistry';

/*
 * Download Apache httpd public registry
 */
if (\in_array('--download', $_SERVER['argv'])) // 403
{
	$tmp = $filename . '.download';
	$file = fopen($tmp, 'w');
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_FILE, $file);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_USERAGENT,
		"Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13");
	$result = curl_exec($curl);
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);
	fclose($file);
	if ($result && $status == 200)
		rename($tmp, $filename);
	else
	{
		unlink($tmp);
		\trigger_error(
			'Failed to download resource file (result: ' .
			TypeConversion::toString($result) . ', status: ' . $status .
			')');
		exit(1);
	}
}

$doc = new \DOMDocument('1.0', 'utf-8');
$doc->load($filename);
$typeMap = [];

$xpath = new \DOMXPath($doc);
$xpath->registerNamespace('r', 'http://www.iana.org/assignments');
/** @var \DOMNodeList */
$registries = $xpath->query('./r:registry', $doc->documentElement);

foreach ($registries as $registry)
{
	/** @var \DOMNode $registry */

	$type = $registry->getAttribute('id');

	$typeMap[$type] = [];

	$records = $xpath->query('r:record', $registry);
	foreach ($records as $record)
	{
		foreach ($records as $record)
		{
			$name = $xpath->query('./r:name', $record);
			if (!$name->length)
				continue;
			$subType = $name->item(0)->nodeValue;

			$typeMap[$type][] = $type . '/' . $subType;
		}
	}
}

foreach ($typeMap as $main => $type)
{
	file_put_contents($outputFileBase . '/' . $main . '.json',
		\json_encode($type));
}


