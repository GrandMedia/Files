<?php declare(strict_types = 1);

namespace GrandMedia\Files\Utils;

use GrandMedia\Files\Exceptions\InvalidFileUploadException;
use GuzzleHttp\Stream\Stream;
use Nette\Http\FileUpload;

final class StreamFactory
{

	use \Nette\StaticClass;

	public static function fromFileUpload(FileUpload $fileUpload): Stream
	{
		if (!$fileUpload->isOk()) {
			throw new InvalidFileUploadException('FileUpload is not Ok.');
		}

		return self::fromPath($fileUpload->getTemporaryFile());
	}

	public static function fromPath(string $path): Stream
	{
		return new Stream(\fopen($path, 'r'));
	}

	public static function fromString(string $content): Stream
	{
		return Stream::factory($content);
	}

}
