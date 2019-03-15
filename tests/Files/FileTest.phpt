<?php declare(strict_types = 1);

namespace GrandMediaTests\Files;

use GrandMedia\Files\File;

require_once __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
final class FileTest extends \Tester\TestCase
{

	/**
	 * @dataProvider getInvalidIds
	 * @throws \Assert\InvalidArgumentException
	 */
	public function testIdRegex(string $id): void
	{
		new File($id, 'asdf', 'adf');
	}

	/**
	 * @throws \Assert\InvalidArgumentException
	 */
	public function testBlankName(): void
	{
		new File('asdf', '', 'asdf');
	}

	/**
	 * @dataProvider getInvalidNamespaces
	 * @throws \Assert\InvalidArgumentException
	 */
	public function testNamespaceRegex(string $namespace): void
	{
		new File('asdf', 'adf', $namespace);
	}

	public function getInvalidIds(): array
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

	public function getInvalidNamespaces(): array
	{
		return [
			[''],
			['file name'],
			['file$name'],
			['file\name'],
			['filečname'],
		];
	}

}

(new FileTest())->run();
