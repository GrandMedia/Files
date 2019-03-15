<?php declare(strict_types = 1);

namespace GrandMedia\Files\Exceptions;

use GrandMedia\Files\File;
use GrandMedia\Files\Version;
use function Safe\sprintf;

final class Helpers
{

	public static function constructFileName(File $file, ?Version $version): string
	{
		$fileName = $file->getId();
		if ($version !== null) {
			$fileName .= sprintf('(%s)', (string) $version);
		}

		return $fileName;
	}

}
