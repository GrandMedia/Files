<?php declare(strict_types = 1);

namespace GrandMediaTests\Files;

use GrandMedia\Files\File;
use GrandMedia\Files\FilesManager;
use GrandMedia\Files\Version;
use GrandMediaTests\Files\Helpers\StreamFactory;
use GrandMediaTests\Files\Mocks\MemoryStorage;
use GuzzleHttp\Psr7\Stream;
use Tester\Assert;
use Tester\FileMock;

require_once __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
final class FilesManagerTest extends \Tester\TestCase
{

	private const DATA_1 = 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.';
	private const DATA_2 = 'Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s.';

	public function testSave(): void
	{
		$manager = $this->createManager();
		$file = File::fromValues('1', '1', '1');

		$manager->save(StreamFactory::createFromString(self::DATA_1), true, true, $file);
		Assert::same(self::DATA_1, (string) $manager->getStream($file));
		Assert::true($manager->isPublic($file));

		$manager->save(StreamFactory::createFromString(self::DATA_2), true, false, $file);
		Assert::same(self::DATA_2, (string) $manager->getStream($file));
		Assert::false($manager->isPublic($file));
	}

	/**
	 * @throws \GrandMedia\Files\Exceptions\InvalidStream
	 */
	public function testSaveNotReadableStream(): void
	{
		$manager = $this->createManager();
		$file = File::fromValues('1', '1', '1');

		$manager->save(new Stream(\fopen(FileMock::create(self::DATA_1), 'wb')), true, true, $file);
	}

	/**
	 * @throws \GrandMedia\Files\Exceptions\InvalidFile
	 */
	public function testSaveWithoutRewrite(): void
	{
		$manager = $this->createManager(['1' => ['' => self::DATA_1]]);
		$file = File::fromValues('1', '1', '1');

		$manager->save(StreamFactory::createFromString(self::DATA_2), false, true, $file);
	}

	public function testDelete(): void
	{
		$manager = $this->createManager(['1' => ['' => self::DATA_1, 'v1' => self::DATA_2]]);
		$file = File::fromValues('1', '1', '1');

		$manager->delete($file);
		Assert::false($manager->exists($file));
		Assert::false($manager->exists($file, Version::from('v1')));
	}

	public function testDeleteVersion(): void
	{
		$manager = $this->createManager(['1' => ['' => self::DATA_1]]);
		$file = File::fromValues('1', '1', '1');

		$manager->deleteVersion($file);
		Assert::false($manager->exists($file));
	}

	/**
	 * @throws \GrandMedia\Files\Exceptions\InvalidFile
	 */
	public function testDeleteVersionNotExists(): void
	{
		$this->createManager()->deleteVersion(File::fromValues('1', '1', '1'));
	}

	public function testGetStream(): void
	{
		$manager = $this->createManager(['1' => ['' => self::DATA_1]]);
		$file = File::fromValues('1', '1', '1');

		Assert::same(self::DATA_1, (string) $manager->getStream($file));
	}

	/**
	 * @throws \GrandMedia\Files\Exceptions\InvalidFile
	 */
	public function testGetStreamNotExists(): void
	{
		$this->createManager()->getStream(File::fromValues('1', '1', '1'));
	}

	/**
	 * @throws \GrandMedia\Files\Exceptions\InvalidStream
	 */
	public function testGetWritableStream(): void
	{
		$manager = new FilesManager(new MemoryStorage(['1' => ['' => self::DATA_1]], [], true));
		$file = File::fromValues('1', '1', '1');

		$manager->getStream($file);
	}

	public function testContentType(): void
	{
		$manager = $this->createManager(['1' => ['' => self::DATA_1]]);
		$file = File::fromValues('1', '1', '1');

		Assert::same(MemoryStorage::CONTENT_TYPE, $manager->getContentType($file));
	}

	/**
	 * @throws \GrandMedia\Files\Exceptions\InvalidFile
	 */
	public function testGetContentTypeNotExists(): void
	{
		$this->createManager()->getContentType(File::fromValues('1', '1', '1'));
	}

	public function testPublicUrl(): void
	{
		$manager = $this->createManager(['1' => ['' => self::DATA_1]], ['1' => ['' => self::DATA_1]]);
		$file = File::fromValues('1', '1', '1');

		Assert::same(MemoryStorage::PUBLIC_URL . '1', $manager->getPublicUrl($file));
	}

	/**
	 * @throws \GrandMedia\Files\Exceptions\InvalidFile
	 */
	public function testGetPublicUrlNotExists(): void
	{
		$this->createManager()->getPublicUrl(File::fromValues('1', '1', '1'));
	}

	public function testSize(): void
	{
		$manager = $this->createManager(['1' => ['' => self::DATA_1]]);
		$file = File::fromValues('1', '1', '1');

		Assert::same(\strlen(self::DATA_1), $manager->getSize($file));
	}

	/**
	 * @throws \GrandMedia\Files\Exceptions\InvalidFile
	 */
	public function testGetSizeNotExists(): void
	{
		$this->createManager()->getSize(File::fromValues('1', '1', '1'));
	}

	public function testGetVersions(): void
	{
		$manager = $this->createManager(['1' => ['v1' => self::DATA_1, 'v2' => self::DATA_2]]);
		$file = File::fromValues('1', '1', '1');

		Assert::equal([Version::from('v1'), Version::from('v2')], $manager->getVersions($file));
	}

	/**
	 * @param  string[][] $files
	 * @param  string[][] $publicFiles
	 */
	private function createManager(array $files = [], array $publicFiles = []): FilesManager
	{
		return new FilesManager(new MemoryStorage($files, $publicFiles));
	}

}

(new FilesManagerTest())->run();
