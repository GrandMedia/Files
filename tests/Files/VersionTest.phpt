<?php declare(strict_types = 1);

namespace GrandMediaTests\Files;

use GrandMedia\Files\Version;

require_once __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
final class VersionTest extends \Tester\TestCase
{

	/**
	 * @dataProvider getInvalidValues
	 * @throws \Assert\InvalidArgumentException
	 */
	public function testVersionRegex(string $value): void
	{
		Version::from($value);
	}

	public function getInvalidValues(): array
	{
		return [
			[''],
			['file name'],
			['file$name'],
			['file/name'],
			['file\name'],
			['filečname'],
		];
	}

}

(new VersionTest())->run();
