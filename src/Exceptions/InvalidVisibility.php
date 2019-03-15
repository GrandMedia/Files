<?php declare(strict_types = 1);

namespace GrandMedia\Files\Exceptions;

use GrandMedia\Files\File;
use GrandMedia\Files\Version;
use function Safe\sprintf;

final class InvalidVisibility extends \InvalidArgumentException
{

	public static function notPublic(File $file, ?Version $version): self
	{
		return new self(sprintf('File %s is not public.', Helpers::constructFileName($file, $version)));
	}

}
