<?php declare(strict_types = 1);

namespace GrandMedia\Files\Storages;

use GrandMedia\Files\Exceptions\InvalidDirectory;
use Nette\Utils\FileSystem;
use function Safe\realpath;

final class WritableDirectory
{

	/** @var string */
	private $directory;

	public function __construct(string $directory)
	{
		if (!FileSystem::isAbsolute($directory)) {
			throw new InvalidDirectory(\sprintf('Directory %s is not an absolute path.', $directory));
		}
		if (!@\is_dir($directory)) {
			throw new InvalidDirectory(\sprintf('Directory %s is not a directory.', $directory));
		}
		if (!@\is_writable($directory)) {
			throw new InvalidDirectory(\sprintf('Directory %s is not writable.', $directory));
		}

		$this->directory = realpath($directory);
	}

	public function __toString(): string
	{
		return $this->directory;
	}

}
