<?php declare(strict_types = 1);

namespace GrandMediaTests\Files\Storages;

use GrandMedia\Files\Exceptions\InvalidDirectoryException;
use GrandMedia\Files\Storages\WritableDirectory;
use Nette\Utils\FileSystem;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
final class WritableDirectoryTest extends \Tester\TestCase
{

	private const DIR = TEMP_DIR . '/test';
	private const NOT_DIR = TEMP_DIR . '/not-exists';
	private const NOT_WRITABLE_DIR = TEMP_DIR . '/not-writable';

	/** @throws \GrandMedia\Files\Exceptions\InvalidDirectoryException */
	public function testNotAbsoluteFilesPath(): void
	{
		new WritableDirectory('..');
	}

	/** @throws \GrandMedia\Files\Exceptions\InvalidDirectoryException */
	public function testNotDirectory(): void
	{
		new WritableDirectory(self::NOT_DIR);
	}

	/** @throws \GrandMedia\Files\Exceptions\InvalidDirectoryException */
	public function testNotWritableDirFilesPath(): void
	{
		FileSystem::createDir(self::NOT_WRITABLE_DIR, '0555');

		try {
			new WritableDirectory(self::NOT_WRITABLE_DIR);
		} catch (InvalidDirectoryException $e) {
			throw $e;
		} finally {
			\rmdir(self::NOT_WRITABLE_DIR);
		}
	}

	public function testToString(): void
	{
		FileSystem::createDir(self::DIR);

		$directory = new WritableDirectory(self::DIR . '/./');
		Assert::same(self::DIR, (string) $directory);
	}

}

(new WritableDirectoryTest())->run();
