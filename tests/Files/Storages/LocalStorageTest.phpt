<?php declare(strict_types = 1);

namespace GrandMediaTests\Files\Storages;

use GrandMedia\Files\File;
use GrandMedia\Files\Storages\LocalStorage;
use GrandMedia\Files\Version;
use GuzzleHttp\Stream\Stream;
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
	private const NOT_DIR = \TEMP_DIR . '/not-exists';
	private const NOT_WRITABLE_DIR = \TEMP_DIR . '/not-writable';
	private const DATA_1 = 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.';
	private const DATA_2 = 'Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s.';

	/**
	 * @throws \Assert\InvalidArgumentException
	 */
	public function testFilesNotDir(): void
	{
		new LocalStorage(self::NOT_DIR, self::PUBLIC_DIR);
	}

	/**
	 * @throws \Assert\InvalidArgumentException
	 */
	public function testFilesNotWritableDir(): void
	{
		new LocalStorage(self::NOT_WRITABLE_DIR, self::PUBLIC_DIR);
	}

	/**
	 * @throws \Assert\InvalidArgumentException
	 */
	public function testPublicNotDir(): void
	{
		new LocalStorage(self::FILES_DIR, self::NOT_DIR);
	}

	/**
	 * @throws \Assert\InvalidArgumentException
	 */
	public function testPublicNotWritableDir(): void
	{
		new LocalStorage(self::FILES_DIR, self::NOT_WRITABLE_DIR);
	}

	public function testSave(): void
	{
		$storage = $this->createStorage();
		$file = new File('12345ab', 'test.txt', 'name/space/foo');

		$storage->save(Stream::factory(self::DATA_1), $file, null);
		Assert::true(\file_exists(self::FILES_DIR . '/name/space/foo/12345/ab/12345ab'));
		Assert::same(self::DATA_1, \file_get_contents(self::FILES_DIR . '/name/space/foo/12345/ab/12345ab'));

		$storage->save(Stream::factory(self::DATA_2), $file, null);
		Assert::same(self::DATA_2, \file_get_contents(self::FILES_DIR . '/name/space/foo/12345/ab/12345ab'));
	}

	public function testSaveVersion(): void
	{
		$storage = $this->createStorage();
		$file = new File('12345ab', 'test.txt', 'name/space/foo');
		$version = Version::from('v1');

		$storage->save(Stream::factory(self::DATA_1), $file, $version);
		Assert::true(\file_exists(self::FILES_DIR . '/name/space/foo/12345/ab/12345ab_v1'));
		Assert::same(self::DATA_1, \file_get_contents(self::FILES_DIR . '/name/space/foo/12345/ab/12345ab_v1'));
	}

	public function testDelete(): void
	{
		$storage = $this->createStorage();
		$file = new File('12345ab', 'test.txt', 'name/space/foo');
		$version = Version::from('v1');

		$storage->save(Stream::factory(self::DATA_1), $file, null);
		$storage->setPublic($file, null);
		$storage->save(Stream::factory(self::DATA_2), $file, $version);
		$storage->setPublic($file, $version);

		$storage->delete($file, $version);
		Assert::false(\file_exists(self::FILES_DIR . '/name/space/foo/12345/ab/12345ab_v1'));
		Assert::true(\file_exists(self::FILES_DIR . '/name/space/foo/12345/ab/12345ab'));
		Assert::false(\file_exists(self::PUBLIC_DIR . '/name/space/foo/12345/ab/test_v1.txt'));
		Assert::true(\file_exists(self::PUBLIC_DIR . '/name/space/foo/12345/ab/test.txt'));

		$storage->delete($file, null);
		Assert::false(\file_exists(self::FILES_DIR . '/name/space/foo/12345/ab/12345ab'));
		Assert::false(\file_exists(self::FILES_DIR . '/name/space/foo/12345'));
		Assert::false(\file_exists(self::FILES_DIR . '/name/space/foo'));
		Assert::true(\file_exists(self::FILES_DIR));
		Assert::false(\file_exists(self::PUBLIC_DIR . '/name/space/foo/12345/ab/test.txt'));
		Assert::false(\file_exists(self::PUBLIC_DIR . '/name/space/foo/12345'));
		Assert::false(\file_exists(self::PUBLIC_DIR . '/name/space/foo'));
		Assert::true(\file_exists(self::PUBLIC_DIR));
	}

	public function testSetPublic(): void
	{
		$storage = $this->createStorage();
		$file = new File('12345ab', 'test.txt', 'name/space/foo');

		$storage->save(Stream::factory(self::DATA_1), $file, null);
		$storage->setPublic($file, null);

		Assert::true(\file_exists(self::PUBLIC_DIR . '/name/space/foo/12345/ab/test.txt'));
		Assert::same(self::DATA_1, \file_get_contents(self::PUBLIC_DIR . '/name/space/foo/12345/ab/test.txt'));
	}

	public function testSetPrivate(): void
	{
		$storage = $this->createStorage();
		$file = new File('12345ab', 'test.txt', 'name/space/foo');

		$storage->save(Stream::factory(self::DATA_1), $file, null);
		$storage->setPublic($file, null);
		$storage->setPrivate($file, null);

		Assert::false(\file_exists(self::PUBLIC_DIR . '/name/space/foo/12345/ab/test.txt'));
	}

	public function testGetStream(): void
	{
		$storage = $this->createStorage();
		$file = new File('12345ab', 'test.txt', 'name/space/foo');

		$storage->save(Stream::factory(self::DATA_1), $file, null);

		Assert::same(self::DATA_1, (string) $storage->getStream($file, null));
	}

	public function testGetContentType(): void
	{
		$storage = $this->createStorage();
		$file = new File('12345ab', 'test.txt', 'name/space/foo');

		$storage->save(Stream::factory(self::DATA_1), $file, null);

		Assert::same('text/plain', $storage->getContentType($file, null));
	}

	public function testGetPublicUrl(): void
	{
		$storage = $this->createStorage();
		$file = new File('12345ab', 'test.txt', 'name/space/foo');

		$storage->save(Stream::factory(self::DATA_1), $file, null);
		$storage->setPublic($file, null);

		Assert::same('name/space/foo/12345/ab/test.txt', $storage->getPublicUrl($file, null));
		Assert::same('1/1/1', $storage->getPublicUrl(new File('1', '1', '1'), null));
	}

	public function testGetSize(): void
	{
		$storage = $this->createStorage();
		$file = new File('12345ab', 'test.txt', 'name/space/foo');

		$storage->save(Stream::factory(self::DATA_1), $file, null);

		Assert::same(\strlen(self::DATA_1), $storage->getSize($file, null));
	}

	public function testExists(): void
	{
		$storage = $this->createStorage();
		$file1 = new File('12345ab', 'test.txt', 'name/space/foo');
		$file2 = new File('12345ac', 'test.txt', 'name/space/foo');

		$storage->save(Stream::factory(self::DATA_1), $file1, null);

		Assert::true($storage->exists($file1, null));
		Assert::false($storage->exists($file2, null));
	}

	public function testIsPublic(): void
	{
		$storage = $this->createStorage();
		$file1 = new File('12345ab', 'test.txt', 'name/space/foo');
		$file2 = new File('12345ac', 'test.txt', 'name/space/foo');

		$storage->save(Stream::factory(self::DATA_1), $file1, null);
		$storage->setPublic($file1, null);

		Assert::true($storage->isPublic($file1, null));
		Assert::false($storage->isPublic($file2, null));
	}

	public function testGetVersions(): void
	{
		$storage = $this->createStorage();
		$file = new File('12345ab', 'test.txt', 'name/space/foo');

		$versionV1 = Version::from('v1');
		$versionV2 = Version::from('v2');

		$storage->save(Stream::factory(self::DATA_1), $file, $versionV1);
		$storage->save(Stream::factory(self::DATA_2), $file, $versionV2);

		Assert::equal([$versionV1, $versionV2], $storage->getVersions($file));
		Assert::equal([], $storage->getVersions(new File('1', '1', '1')));
	}

	protected function setUp(): void
	{
		parent::setUp();

		FileSystem::createDir(self::FILES_DIR);
		FileSystem::createDir(self::PUBLIC_DIR);
		FileSystem::createDir(self::NOT_WRITABLE_DIR, '0555');
	}

	protected function tearDown(): void
	{
		parent::tearDown();

		\rmdir(self::NOT_WRITABLE_DIR);
	}

	private function createStorage(): LocalStorage
	{
		return new LocalStorage(self::FILES_DIR, self::PUBLIC_DIR);
	}

}

(new LocalStorageTest())->run();
