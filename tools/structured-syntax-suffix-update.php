<?php
use NoreSources\Container\Container;
use NoreSources\Type\TypeConversion;

require (__DIR__ . '/../vendor/autoload.php');

$url = 'https://www.iana.org/assignments/media-type-structured-suffix/structured-syntax-suffix.csv';
$filename = __DIR__ . '/../resources/iana/structured-syntax-suffix.csv';

/*
 * Download IANA registry
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

/*
 * Update source code
 */

$file = fopen($filename, 'r');
$suffixes = [];
$rowIndex = 0;
while ($row = \fgetcsv($file))
{
	if ($rowIndex++ == 0)
		continue;
	$description = \addslashes($row[0]);
	$suffix = \substr($row[1], 1);

	$suffixes[$suffix] = $description;
}
fclose($file);

ksort($suffixes);
$suffixes = Container::implode($suffixes,
	[
		Container::IMPLODE_BETWEEN => ', ' . PHP_EOL . "\t\t\t\t"
	],
	function ($k, $v) {
		return "'" . $k . "' => '" . \str_replace("'", '\\\'', $v) . "'";
	});

$sourceFile = __DIR__ . '/../src/StructuredSyntaxSuffixRegistry.php';

$source = file_get_contents($sourceFile);

$source = \preg_replace(
	chr(1) . '(--<<sufixes>>--)(.*?)(--<</sufixes>>--)' . chr(1) . 'sm',
	'\1*/' . $suffixes . '/*\3', $source);

file_put_contents($sourceFile, $source);
