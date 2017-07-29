<?php declare(strict_types = 1);

namespace GrandMediaTests\Files;

use GrandMedia\Files\File;
use GrandMedia\Files\FilesManager;
use GrandMedia\Files\Utils\StreamFactory;
use GrandMediaTests\Files\Mocks\FilesStorage;
use GrandMediaTests\Files\Mocks\HttpRequest;
use GrandMediaTests\Files\Mocks\HttpResponse;
use GuzzleHttp\Stream\Stream;
use Tester\Assert;
use Tester\FileMock;

require_once __DIR__ . '/bootstrap.php';

/**
 * @testCase
 */
final class FilesManagerTest extends \Tester\TestCase
{

	private const DATA_1 = 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.';
	private const DATA_2 = 'Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s.';

	public function testSave(): void
	{
		$manager = new FilesManager(new FilesStorage());
		$file = new File('1', '1', '1', true);

		$manager->save($file, StreamFactory::fromString(self::DATA_1), true);
		Assert::same(self::DATA_1, (string) $manager->getStream($file));

		$manager->save($file, StreamFactory::fromString(self::DATA_2), true);
		Assert::same(self::DATA_2, (string) $manager->getStream($file));
	}

	/** @throws \GrandMedia\Files\Exceptions\InvalidStreamException */
	public function testSaveNotReadableStream(): void
	{
		$manager = new FilesManager(new FilesStorage());
		$file = new File('1', '1', '1', true);

		$manager->save($file, new Stream(\fopen(FileMock::create(self::DATA_1), 'w')), true);
	}

	/** @throws \GrandMedia\Files\Exceptions\InvalidFileException */
	public function testSaveWithoutRewrite(): void
	{
		$manager = new FilesManager(new FilesStorage());
		$file = new File('1', '1', '1', true);

		$manager->save($file, StreamFactory::fromString(self::DATA_1), false);
		$manager->save($file, StreamFactory::fromString(self::DATA_2), false);
	}

	public function testDelete(): void
	{
		$storage = new FilesStorage(['1' => ['original' => self::DATA_1]]);
		$manager = new FilesManager($storage);
		$file = new File('1', '1', '1', true);

		$manager->delete($file);
		Assert::false($storage->exists($file));
	}

	public function testGetStreamResponse(): void
	{
		$manager = new FilesManager(new FilesStorage(['1' => ['original' => self::DATA_1]]));
		$file = new File('1', '1', '1', true);

		$streamResponse = $manager->getStreamResponse($file, true);
		\ob_start();
		$streamResponse->send(new HttpRequest(), new HttpResponse());
		Assert::same(self::DATA_1, \ob_get_clean());
	}

	public function testGetStream(): void
	{
		$storage = new FilesStorage(['1' => ['original' => self::DATA_1]]);
		$manager = new FilesManager($storage);
		$file = new File('1', '1', '1', true);

		Assert::same((string) $storage->getStream($file), (string) $manager->getStream($file));
	}

	/**
	 * @throws \GrandMedia\Files\Exceptions\InvalidStreamException
	 */
	public function testGetWritableStream(): void
	{
		$manager = new FilesManager(new FilesStorage(['1' => ['original' => self::DATA_1]], true));
		$file = new File('1', '1', '1', true);

		$manager->getStream($file);
	}

	public function testContentType(): void
	{
		$storage = new FilesStorage(['1' => ['original' => self::DATA_1]]);
		$manager = new FilesManager($storage);
		$file = new File('1', '1', '1', true);

		Assert::same($storage->getContentType($file), $manager->getContentType($file));
	}

	public function testPublicUrl(): void
	{
		$storage = new FilesStorage(['1' => ['original' => self::DATA_1]]);
		$manager = new FilesManager($storage);
		$file = new File('1', '1', '1', true);

		Assert::same($storage->getPublicUrl($file), $manager->getPublicUrl($file));
	}

	public function testSize(): void
	{
		$storage = new FilesStorage(['1' => ['original' => self::DATA_1]]);
		$manager = new FilesManager($storage);
		$file = new File('1', '1', '1', true);

		Assert::same($storage->getSize($file), $manager->getSize($file));
	}

	public function testVariants(): void
	{
		$storage = new FilesStorage(['1' => ['original' => self::DATA_1]]);
		$manager = new FilesManager($storage);
		$file = new File('1', '1', '1', true);

		Assert::same($storage->getVersions($file), $manager->getVersions($file));
	}

}

(new FilesManagerTest())->run();
