<?php declare(strict_types = 1);

namespace GrandMediaTests\Files\Utils;

use GrandMedia\Files\Utils\StreamFactory;
use Nette\Http\FileUpload;
use Tester\Assert;
use Tester\FileMock;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class StreamFactoryTest extends \Tester\TestCase
{

	private const DATA = 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.';

	public function testFromFileUpload(): void
	{
		$fileUpload = new FileUpload(
			[
				'name' => 'test.txt',
				'type' => '',
				'size' => \strlen(self::DATA),
				'tmp_name' => FileMock::create(self::DATA, 'txt'),
				'error' => UPLOAD_ERR_OK,
			]
		);

		Assert::same(self::DATA, (string) StreamFactory::fromFileUpload($fileUpload));
	}

	/** @throws \GrandMedia\Files\Exceptions\InvalidFileUploadException */
	public function testFromInvalidFileUpload(): void
	{
		StreamFactory::fromFileUpload(new FileUpload([]));
	}

	public function testFromPath(): void
	{
		Assert::same(self::DATA, (string) StreamFactory::fromPath(FileMock::create(self::DATA)));
	}

	public function testFromString(): void
	{
		Assert::same(self::DATA, (string) StreamFactory::fromString(self::DATA));
	}

}

(new StreamFactoryTest())->run();
