<?php declare(strict_types = 1);

namespace GrandMedia\Files\Exceptions;

use GrandMedia\Files\File;
use GrandMedia\Files\Version;

final class InvalidFile extends \InvalidArgumentException
{

	public static function exists(File $file, ?Version $version): self
	{
		return new self(sprintf('File %s already exists.', Helpers::constructFileName($file, $version)));
	}

	public static function notExists(File $file, ?Version $version): self
	{
		return new self(sprintf('File %s does not exist.', Helpers::constructFileName($file, $version)));
	}

}
