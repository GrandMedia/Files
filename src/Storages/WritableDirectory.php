<?php declare(strict_types = 1);

namespace GrandMedia\Files\Storages;

use GrandMedia\Files\Exceptions\InvalidDirectoryException;
use Nette\Utils\FileSystem;

final class WritableDirectory
{

	/** @var string */
	private $directory;

	public function __construct(string $directory)
	{
		if (!FileSystem::isAbsolute($directory)) {
			throw new InvalidDirectoryException(sprintf('Directory %s is not an absolute path.', $directory));
		} elseif (!@\is_dir($directory)) {
			throw new InvalidDirectoryException(sprintf('Directory %s is not a directory.', $directory));
		} elseif (!@\is_writable($directory)) {
			throw new InvalidDirectoryException(sprintf('Directory %s is not writable.', $directory));
		}

		$this->directory = \realpath($directory);
	}

	public function __toString(): string
	{
		return $this->directory;
	}

}
