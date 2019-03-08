<?php declare(strict_types = 1);

namespace GrandMediaTests\Files\Storages;

use GrandMedia\Files\File;
use GrandMedia\Files\Storages\LocalStorage;
use GrandMedia\Files\Storages\WritableDirectory;
use GrandMedia\Files\Utils\StreamFactory;
use Nette\Utils\FileSystem;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class LocalStorageTest extends \Tester\TestCase
{

	private const PUBLIC_DIR = \TEMP_DIR . '/public';
	private const FILES_DIR = \TEMP_DIR . '/files';
	private const DATA_1 = 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.';
	private const DATA_2 = 'Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s.';

	public function testSave(): void
	{
		$storage = $this->createStorage();
		$file = new File('123ab', 'test.txt', 'name/space/foo', true);

		$storage->save($file, StreamFactory::fromString(self::DATA_1));
		Assert::true(\file_exists(self::FILES_DIR . '/name/space/foo/123/ab/original'));
		Assert::same(self::DATA_1, \file_get_contents(self::FILES_DIR . '/name/space/foo/123/ab/original'));

		$storage->save($file, StreamFactory::fromString(self::DATA_2));
		Assert::same(self::DATA_2, \file_get_contents(self::FILES_DIR . '/name/space/foo/123/ab/original'));
	}

	public function testDelete(): void
	{
		$storage = $this->createStorage();
		$file1 = new File('123ab', 'test.txt', 'name/space/foo', true);
		$file2 = new File('123ac', 'test.txt', 'name/space/foo', true);

		$storage->save($file1, StreamFactory::fromString(self::DATA_1));
		$storage->save($file2, StreamFactory::fromString(self::DATA_2));
		$storage->getPublicUrl($file1);
		$storage->getPublicUrl($file2);
		$storage->getPublicUrl(new File('123ab', 'bar.txt', 'name/space/foo', true));

		$storage->delete($file1);
		Assert::false(\file_exists(self::FILES_DIR . '/name/space/foo/123/ab/original'));
		Assert::false(\file_exists(self::FILES_DIR . '/name/space/foo/123/ab'));
		Assert::true(\file_exists(self::FILES_DIR . '/name/space/foo/123/ac/original'));
		Assert::false(\file_exists(self::PUBLIC_DIR . '/name/space/foo/123/ab'));
		Assert::true(\file_exists(self::PUBLIC_DIR . '/name/space/foo/123/ac'));
	}

	/** @throws \GrandMedia\Files\Exceptions\InvalidFile */
	public function testDeleteNotExists(): void
	{
		$this->createStorage()->delete(new File('1', '1', '1', true));
	}

	public function testGetStream(): void
	{
		$storage = $this->createStorage();
		$file = new File('123ab', 'test.txt', 'name/space/foo', true);

		$storage->save($file, StreamFactory::fromString(self::DATA_1));

		Assert::same(self::DATA_1, (string) $storage->getStream($file));
	}

	/** @throws \GrandMedia\Files\Exceptions\InvalidFile */
	public function testGetStreamNotExists(): void
	{
		$this->createStorage()->getStream(new File('1', '1', '1', true));
	}

	public function testGetContentType(): void
	{
		$storage = $this->createStorage();
		$file = new File('123ab', 'test.txt', 'name/space/foo', true);

		$storage->save($file, StreamFactory::fromString(self::DATA_1));

		Assert::same('text/plain', $storage->getContentType($file));
	}

	/** @throws \GrandMedia\Files\Exceptions\InvalidFile */
	public function testGetContentTypeNotExists(): void
	{
		$this->createStorage()->getContentType(new File('1', '1', '1', true));
	}

	public function testGetPublicUrl(): void
	{
		$storage = $this->createStorage();
		$file1 = new File('123ab', 'test.txt', 'name/space/foo', true);
		$file1OtherName = new File('123ab', 'foo.txt', 'name/space/foo', true);
		$file2 = new File('123ac', 'test.txt', 'name/space/foo', false);

		$storage->save($file1, StreamFactory::fromString(self::DATA_1));

		Assert::same('name/space/foo/123/ab/original/test.txt', $storage->getPublicUrl($file1));
		Assert::true(\file_exists(self::PUBLIC_DIR . '/name/space/foo/123/ab/original/test.txt'));
		Assert::same('name/space/foo/123/ab/original/foo.txt', $storage->getPublicUrl($file1OtherName));
		Assert::true(\file_exists(self::PUBLIC_DIR . '/name/space/foo/123/ab/original/foo.txt'));

		$storage->save($file2, StreamFactory::fromString(self::DATA_2));

		Assert::same('', $storage->getPublicUrl($file2));
		Assert::false(\file_exists(self::PUBLIC_DIR . '/name/space/foo/123/ac/original/test.txt'));
	}

	/** @throws \GrandMedia\Files\Exceptions\InvalidFile */
	public function testGetPublicUrlNotExists(): void
	{
		$this->createStorage()->getPublicUrl(new File('1', '1', '1', true));
	}

	public function testGetSize(): void
	{
		$storage = $this->createStorage();
		$file = new File('123ab', 'test.txt', 'name/space/foo', true);

		$storage->save($file, StreamFactory::fromString(self::DATA_1));

		Assert::same(\strlen(self::DATA_1), $storage->getSize($file));
	}

	/** @throws \GrandMedia\Files\Exceptions\InvalidFile */
	public function testGetSizeNotExists(): void
	{
		$this->createStorage()->getSize(new File('1', '1', '1', true));
	}

	public function testExists(): void
	{
		$storage = $this->createStorage();
		$file1 = new File('123ab', 'test.txt', 'name/space/foo', true);
		$file2 = new File('123ac', 'test.txt', 'name/space/foo', true);

		$storage->save($file1, StreamFactory::fromString(self::DATA_1));

		Assert::true($storage->exists($file1));
		Assert::false($storage->exists($file2));
	}

	public function testGetVersions(): void
	{
		$storage = $this->createStorage();
		$file = new File('123ab', 'test.txt', 'name/space/foo', true);
		$fileVersion = new File('123ab', 'test.txt', 'name/space/foo', true, 'original_v1');

		$storage->save($file, StreamFactory::fromString(self::DATA_1));
		$storage->save($fileVersion, StreamFactory::fromString(self::DATA_2));

		$versions = $storage->getVersions($file);
		\sort($versions);
		Assert::equal(['original', 'original_v1'], $versions);
		Assert::equal(['original_v1'], $storage->getVersions($fileVersion));
	}

	/** @throws \GrandMedia\Files\Exceptions\InvalidFile */
	public function testGetVersionsNotExists(): void
	{
		$this->createStorage()->getVersions(new File('1', '1', '1', true));
	}

	protected function setUp(): void
	{
		parent::setUp();

		FileSystem::createDir(self::FILES_DIR);
		FileSystem::createDir(self::PUBLIC_DIR);
	}

	private function createStorage(): LocalStorage
	{
		return new LocalStorage(new WritableDirectory(self::FILES_DIR), new WritableDirectory(self::PUBLIC_DIR));
	}

}

(new LocalStorageTest())->run();
