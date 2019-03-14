<?php declare(strict_types = 1);

namespace GrandMediaTests\Files;

use GrandMedia\Files\File;
use GrandMedia\Files\FilesManager;
use GrandMedia\Files\Version;
use GrandMediaTests\Files\Mocks\MemoryStorage;
use GuzzleHttp\Stream\Stream;
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
		$file = new File('1', '1', '1', true);

		$manager->save(Stream::factory(self::DATA_1), true, $file);
		Assert::same(self::DATA_1, (string) $manager->getStream($file));

		$manager->save(Stream::factory(self::DATA_2), true, $file);
		Assert::same(self::DATA_2, (string) $manager->getStream($file));
	}

	/**
	 * @throws \GrandMedia\Files\Exceptions\InvalidStream
	 */
	public function testSaveNotReadableStream(): void
	{
		$manager = $this->createManager();
		$file = new File('1', '1', '1', true);

		$manager->save(new Stream(\fopen(FileMock::create(self::DATA_1), 'wb')), true, $file);
	}

	/**
	 * @throws \GrandMedia\Files\Exceptions\InvalidFile
	 */
	public function testSaveWithoutRewrite(): void
	{
		$manager = $this->createManager(['1' => ['' => self::DATA_1]]);
		$file = new File('1', '1', '1', true);

		$manager->save(Stream::factory(self::DATA_2), false, $file);
	}

	public function testDelete(): void
	{
		$manager = $this->createManager(['1' => ['' => self::DATA_1, 'v1' => self::DATA_2]]);
		$file = new File('1', '1', '1', true);

		$manager->delete($file);
		Assert::false($manager->exists($file));
		Assert::false($manager->exists($file, Version::from('v1')));
	}

	public function testDeleteNotExists(): void
	{
		$this->createManager()->delete(new File('1', '1', '1', true));

		Assert::true(true);
	}

	public function testDeleteVersion(): void
	{
		$manager = $this->createManager(['1' => ['' => self::DATA_1]]);
		$file = new File('1', '1', '1', true);

		$manager->deleteVersion($file);
		Assert::false($manager->exists($file));
	}

	/**
	 * @throws \GrandMedia\Files\Exceptions\InvalidFile
	 */
	public function testDeleteVersionNotExists(): void
	{
		$this->createManager()->deleteVersion(new File('1', '1', '1', true));
	}

	public function testGetStream(): void
	{
		$manager = $this->createManager(['1' => ['' => self::DATA_1]]);
		;
		$file = new File('1', '1', '1', true);

		Assert::same(self::DATA_1, (string) $manager->getStream($file));
	}

	/**
	 * @throws \GrandMedia\Files\Exceptions\InvalidFile
	 */
	public function testGetStreamNotExists(): void
	{
		$this->createManager()->getStream(new File('1', '1', '1', true));
	}

	/**
	 * @throws \GrandMedia\Files\Exceptions\InvalidStream
	 */
	public function testGetWritableStream(): void
	{
		$manager = new FilesManager(new MemoryStorage(['1' => ['' => self::DATA_1]], true));
		$file = new File('1', '1', '1', true);

		$manager->getStream($file);
	}

	public function testContentType(): void
	{
		$manager = $this->createManager(['1' => ['' => self::DATA_1]]);
		$file = new File('1', '1', '1', true);

		Assert::same(MemoryStorage::CONTENT_TYPE, $manager->getContentType($file));
	}

	/**
	 * @throws \GrandMedia\Files\Exceptions\InvalidFile
	 */
	public function testGetContentTypeNotExists(): void
	{
		$this->createManager()->getContentType(new File('1', '1', '1', true));
	}

	public function testPublicUrl(): void
	{
		$manager = $this->createManager(['1' => ['' => self::DATA_1]]);
		$file = new File('1', '1', '1', true);

		Assert::same(MemoryStorage::PUBLIC_URL . '1', $manager->getPublicUrl($file));
	}

	/**
	 * @throws \GrandMedia\Files\Exceptions\InvalidFile
	 */
	public function testGetPublicUrlNotExists(): void
	{
		$this->createManager()->getPublicUrl(new File('1', '1', '1', true));
	}

	public function testSize(): void
	{
		$manager = $this->createManager(['1' => ['' => self::DATA_1]]);
		$file = new File('1', '1', '1', true);

		Assert::same(\strlen(self::DATA_1), $manager->getSize($file));
	}

	/**
	 * @throws \GrandMedia\Files\Exceptions\InvalidFile
	 */
	public function testGetSizeNotExists(): void
	{
		$this->createManager()->getSize(new File('1', '1', '1', true));
	}

	public function testGetVersions(): void
	{
		$manager = $this->createManager(['1' => ['v1' => self::DATA_1, 'v2' => self::DATA_2]]);
		$file = new File('1', '1', '1', true);

		Assert::equal([Version::from('v1'), Version::from('v2')], $manager->getVersions($file));
	}

	/**
	 * @param  string[][] $files
	 */
	private function createManager(array $files = []): FilesManager
	{
		return new FilesManager(new MemoryStorage($files));
	}

}

(new FilesManagerTest())->run();
