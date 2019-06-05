<?php declare(strict_types = 1);

namespace GrandMediaTests\Files\Helpers;

use GuzzleHttp\Psr7\Stream;
use function Safe\fopen;
use function Safe\fwrite;

final class StreamFactory
{

	public static function createFromString(string $data): Stream
	{
		$resource = fopen('php://temp', 'rb+');
		fwrite($resource, $data);
		\fseek($resource, 0);

		return new Stream($resource);
	}

}
