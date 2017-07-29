<?php declare(strict_types = 1);

namespace GrandMediaTests\Files;

use GrandMedia\Files\File;
use GrandMedia\Files\FilesManager;
use GrandMedia\Files\Storages\LocalStorage;
use GrandMedia\Files\Storages\WritableDirectory;
use GrandMedia\Files\Utils\StreamFactory;
use GrandMediaTests\Files\Mocks\HttpRequest;
use GrandMediaTests\Files\Mocks\HttpResponse;
use GuzzleHttp\Stream\Stream;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tester\FileMock;

require_once __DIR__ . '/bootstrap.php';

/**
 * @testCase
 */
final class FilesManagerTest extends \Tester\TestCase
{

	private const PUBLIC_DIR = TEMP_DIR . '/public';
	private const FILES_DIR = TEMP_DIR . '/files';
	private const DATA_1 = 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.';
	private const DATA_2 = 'Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s.';

	/** @throws \GrandMedia\Files\Exceptions\InvalidStreamException */
	public function testSaveNotReadableStream(): void
	{
		$manager = new FilesManager($this->createStorage());
		$file = new File('1', '1', '1', true);

		$manager->save($file, new Stream(\fopen(FileMock::create(self::DATA_1), 'w')), true);
	}

	/** @throws \GrandMedia\Files\Exceptions\InvalidFileException */
	public function testSaveWithoutRewrite(): void
	{
		$manager = new FilesManager($this->createStorage());
		$file = new File('1', '1', '1', true);

		$manager->save($file, StreamFactory::fromString(self::DATA_1), false);
		$manager->save($file, StreamFactory::fromString(self::DATA_2), false);
	}

	public function testDelete(): void
	{
		$storage = $this->createStorage();
		$manager = new FilesManager($storage);
		$file = new File('1', '1', '1', true);

		$manager->save($file, StreamFactory::fromString(self::DATA_1), false);

		$manager->delete($file);
		Assert::false($storage->exists($file));
	}

	public function testGetStreamResponse(): void
	{
		$manager = new FilesManager($this->createStorage());
		$file = new File('1', '1', '1', true);

		$manager->save($file, StreamFactory::fromString(self::DATA_1), true);

		$streamResponse = $manager->getStreamResponse($file, true);

		\ob_start();
		$streamResponse->send(new HttpRequest(), new HttpResponse());
		Assert::same(self::DATA_1, \ob_get_clean());
	}

	public function testGetStream(): void
	{
		$storage = $this->createStorage();
		$manager = new FilesManager($storage);
		$file = new File('1', '1', '1', true);

		$manager->save($file, StreamFactory::fromString(self::DATA_1), true);

		Assert::same((string) $storage->getStream($file), (string) $manager->getStream($file));
	}

	public function testContentType(): void
	{
		$storage = $this->createStorage();
		$manager = new FilesManager($storage);
		$file = new File('1', '1', '1', true);

		$manager->save($file, StreamFactory::fromString(self::DATA_1), true);

		Assert::same($storage->getContentType($file), $manager->getContentType($file));
	}

	public function testPublicUrl(): void
	{
		$storage = $this->createStorage();
		$manager = new FilesManager($storage);
		$file = new File('1', '1', '1', true);

		$manager->save($file, StreamFactory::fromString(self::DATA_1), true);

		Assert::same($storage->getPublicUrl($file), $manager->getPublicUrl($file));
	}

	public function testSize(): void
	{
		$storage = $this->createStorage();
		$manager = new FilesManager($storage);
		$file = new File('1', '1', '1', true);

		$manager->save($file, StreamFactory::fromString(self::DATA_1), true);

		Assert::same($storage->getSize($file), $manager->getSize($file));
	}

	public function testVariants(): void
	{
		$storage = $this->createStorage();
		$manager = new FilesManager($storage);
		$file = new File('1', '1', '1', true);

		$manager->save($file, StreamFactory::fromString(self::DATA_1), true);

		Assert::same($storage->getVersions($file), $manager->getVersions($file));
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

(new FilesManagerTest())->run();
