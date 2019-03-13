<?php declare(strict_types = 1);

namespace GrandMedia\Files\Storages;

use Assert\Assertion;
use FilesystemIterator;
use GrandMedia\Files\Exceptions\InvalidFile;
use GrandMedia\Files\File;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Stream\StreamInterface;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Nette\Utils\Strings;
use function Safe\filesize;
use function Safe\fopen;
use function Safe\realpath;

final class LocalStorage implements \GrandMedia\Files\Storage
{

	private const ID_CHUNK_LENGTH = 3;
	private const BUFFER_LENGTH = 8192;

	/**
	 * @var string
	 */
	private $filesDirectory;

	/**
	 * @var string
	 */
	private $publicDirectory;

	public function __construct(string $filesDirectory, string $publicDirectory)
	{
		Assertion::directory($filesDirectory);
		Assertion::writeable($filesDirectory);
		Assertion::directory($publicDirectory);
		Assertion::writeable($publicDirectory);

		$this->filesDirectory = realpath($filesDirectory);
		$this->publicDirectory = realpath($publicDirectory);
	}

	public function save(File $file, StreamInterface $stream): void
	{
		$filePath = $this->getFilePath($file);

		FileSystem::createDir(\dirname($filePath));

		$newStream = new Stream(fopen('nette.safe://' . $filePath, 'wb'));
		$stream->seek(0);
		while (!$stream->eof()) {
			$newStream->write($stream->read(self::BUFFER_LENGTH));
		}
		$newStream->close();
	}

	public function delete(File $file): void
	{
		$this->checkExists($file);
		$filePath = $this->getFilePath($file);

		FileSystem::delete($filePath);
		$this->deleteEmptyDirectories(\dirname($filePath), $this->filesDirectory);

		if ($file->isPublic()) {
			$publicDirectory = \dirname($this->getPublicFilePath($file));
			if (\file_exists($publicDirectory)) {
				foreach (Finder::findFiles('*')->from($publicDirectory) as $publicFile => $info) {
					FileSystem::delete($publicFile);
				}

				$this->deleteEmptyDirectories($publicDirectory, $this->publicDirectory);
			}
		}
	}

	public function getStream(File $file): StreamInterface
	{
		$this->checkExists($file);

		return new Stream(fopen('nette.safe://' . $this->getFilePath($file), 'rb'));
	}

	public function getContentType(File $file): string
	{
		$this->checkExists($file);

		return \finfo_file(\finfo_open(\FILEINFO_MIME_TYPE), $this->getFilePath($file));
	}

	public function getPublicUrl(File $file): string
	{
		$this->checkExists($file);
		$filePath = $this->getFilePath($file);

		if (!$file->isPublic()) {
			return '';
		}

		$publicFilePath = $this->getPublicFilePath($file);
		if (!\file_exists($publicFilePath)) {
			FileSystem::copy($filePath, $publicFilePath);
		}

		return Strings::substring(
			$publicFilePath,
			Strings::length($this->publicDirectory) + 1
		);
	}

	public function getSize(File $file): int
	{
		$this->checkExists($file);

		return filesize($this->getFilePath($file));
	}

	/**
	 * @return string[]
	 */
	public function getVersions(File $file): array
	{
		$this->checkExists($file);
		$filePath = $this->getFilePath($file);
		$variants = [];

		$directory = \dirname($filePath);
		if (\file_exists($directory)) {
			/** @var \SplFileInfo $info */
			foreach (Finder::findFiles(\basename($filePath) . '*')->from($directory) as $info) {
				$variants[] = $info->getBasename();
			}
		}

		return $variants;
	}

	public function exists(File $file): bool
	{
		return \file_exists($this->getFilePath($file));
	}

	private function checkExists(File $file): void
	{
		if (!$this->exists($file)) {
			throw new InvalidFile('File does not exists.');
		}
	}

	private function deleteEmptyDirectories(string $directory, string $upToDirectory): void
	{
		$directory = realpath($directory);
		$upToDirectory = realpath($upToDirectory);

		while ($directory !== $upToDirectory && !(new FilesystemIterator($directory))->valid()) {
			FileSystem::delete($directory);

			$parts = \explode('/', $directory);
			\array_pop($parts);
			$directory = $this->joinDirectories($parts);
		}
	}

	private function getPublicFilePath(File $file): string
	{
		$directories = [
			$this->publicDirectory,
			$file->getNamespace(),
		];

		return $this->joinDirectories(
			\array_merge(
				$directories,
				[
					$this->getIdDirectory($file->getId()),
					$file->getVersion(),
					$file->getName(),
				]
			)
		);
	}

	private function getFilePath(File $file): string
	{
		$directories = [
			$this->filesDirectory,
			$file->getNamespace(),
		];

		return $this->joinDirectories(
			\array_merge(
				$directories,
				[
					$this->getIdDirectory($file->getId()),
					$file->getVersion(),
				]
			)
		);
	}

	private function getIdDirectory(string $id): string
	{
		return $this->joinDirectories(\str_split($id, self::ID_CHUNK_LENGTH));
	}

	/**
	 * @param string[] $directories
	 */
	private function joinDirectories(array $directories): string
	{
		return \implode('/', $directories);
	}

}
