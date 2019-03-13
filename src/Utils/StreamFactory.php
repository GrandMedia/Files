<?php declare(strict_types = 1);

namespace GrandMedia\Files\Utils;

use GrandMedia\Files\Exceptions\InvalidFileUpload;
use GuzzleHttp\Stream\Stream;
use Nette\Http\FileUpload;
use function Safe\fopen;

final class StreamFactory
{

	use \Nette\StaticClass;

	public static function fromFileUpload(FileUpload $fileUpload): Stream
	{
		if (!$fileUpload->isOk()) {
			throw new InvalidFileUpload('FileUpload is not Ok.');
		}

		return self::fromPath($fileUpload->getTemporaryFile());
	}

	public static function fromPath(string $path): Stream
	{
		return new Stream(fopen($path, 'rb'));
	}

	public static function fromString(string $content): Stream
	{
		return Stream::factory($content);
	}

}
