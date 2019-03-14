<?php declare(strict_types = 1);

namespace GrandMedia\Files\Storages;

use Assert\Assertion;
use FilesystemIterator;
use GrandMedia\Files\File;
use GrandMedia\Files\Version;
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

	private const ID_CHUNK_LENGTH = 5;
	private const BUFFER_LENGTH = 8192;
	private const VERSION_SEPARATOR = '_';

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

	public function save(StreamInterface $stream, File $file, ?Version $version): void
	{
		$filePath = $this->getFilePath($file, $version);

		FileSystem::createDir(\dirname($filePath));

		$newStream = new Stream(fopen('nette.safe://' . $filePath, 'wb'));
		$stream->seek(0);
		while (!$stream->eof()) {
			$newStream->write($stream->read(self::BUFFER_LENGTH));
		}
		$newStream->close();
	}

	public function delete(File $file, ?Version $version): void
	{
		$filePath = $this->getFilePath($file, $version);

		FileSystem::delete($filePath);
		$this->deleteEmptyDirectories(\dirname($filePath), $this->filesDirectory);

		if ($file->isPublic()) {
			$publicFilePath = $this->getPublicFilePath($file, $version);

			FileSystem::delete($publicFilePath);
			$this->deleteEmptyDirectories(\dirname($publicFilePath), $this->publicDirectory);
		}
	}

	public function getStream(File $file, ?Version $version): StreamInterface
	{
		return new Stream(fopen('nette.safe://' . $this->getFilePath($file, $version), 'rb'));
	}

	public function getContentType(File $file, ?Version $version): string
	{
		return \finfo_file(\finfo_open(\FILEINFO_MIME_TYPE), $this->getFilePath($file, $version));
	}

	public function getPublicUrl(File $file, ?Version $version): string
	{
		$filePath = $this->getFilePath($file, $version);

		if (!$file->isPublic()) {
			return '';
		}

		$publicFilePath = $this->getPublicFilePath($file, $version);
		if (!\file_exists($publicFilePath)) {
			FileSystem::copy($filePath, $publicFilePath);
		}

		return Strings::substring(
			$publicFilePath,
			Strings::length($this->publicDirectory) + 1
		);
	}

	public function getSize(File $file, ?Version $version): int
	{
		return filesize($this->getFilePath($file, $version));
	}

	/**
	 * @return \GrandMedia\Files\Version[]
	 */
	public function getVersions(File $file): array
	{
		$versions = [];

		$fileDirectory = $this->getFileDirectory($file);
		if (\is_dir($fileDirectory)) {
			/** @var \SplFileInfo $info */
			foreach (Finder::findFiles('*')->from($fileDirectory) as $info) {
				if ($info->getBasename() !== $file->getId()) {
					$parts = \explode(self::VERSION_SEPARATOR, $info->getBasename());
					$versions[] = Version::from($parts[\count($parts) - 1]);
				}
			}
		}

		return $versions;
	}

	public function exists(File $file, ?Version $version): bool
	{
		return \file_exists($this->getFilePath($file, $version));
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

	private function getPublicFilePath(File $file, ?Version $version): string
	{
		$fileName = $file->getName();
		if ($version !== null) {
			$parts = \explode('.', $fileName);
			$parts[\count($parts) === 1 ? 0 : \count($parts) - 2] .= self::VERSION_SEPARATOR . $version;

			$fileName = \implode('.', $parts);
		}

		return $this->joinDirectories(
			[
				$this->publicDirectory,
				$file->getNamespace(),
				$this->getIdDirectory($file->getId()),
				$fileName,
			]
		);
	}

	private function getFilePath(File $file, ?Version $version): string
	{
		$fileName = $file->getId();
		if ($version !== null) {
			$fileName .= self::VERSION_SEPARATOR . $version;
		}

		return $this->joinDirectories(
			[
				$this->getFileDirectory($file),
				$fileName,
			]
		);
	}

	private function getFileDirectory(File $file): string
	{
		return $this->joinDirectories(
			[
				$this->filesDirectory,
				$file->getNamespace(),
				$this->getIdDirectory($file->getId()),
			]
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
