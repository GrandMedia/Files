<?php declare(strict_types = 1);

namespace GrandMedia\Files\Storages;

use Assert\Assertion;
use FilesystemIterator;
use GrandMedia\Files\Exceptions\InvalidFile;
use GrandMedia\Files\File;
use GrandMedia\Files\Version;
use GuzzleHttp\Psr7\Stream;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Psr\Http\Message\StreamInterface;
use function Safe\filesize;
use function Safe\finfo_open;
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
	private $privateDirectory;

	/**
	 * @var string
	 */
	private $publicDirectory;

	public function __construct(string $privateDirectory, string $publicDirectory)
	{
		Assertion::directory($privateDirectory);
		Assertion::writeable($privateDirectory);
		Assertion::directory($publicDirectory);
		Assertion::writeable($publicDirectory);

		$this->privateDirectory = realpath($privateDirectory);
		$this->publicDirectory = realpath($publicDirectory);
	}

	public function save(StreamInterface $stream, File $file, ?Version $version, bool $public): void
	{
		$filePath = $public ? $this->getPublicFilePath($file, $version) : $this->getPrivateFilePath($file, $version);

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
		$this->deleteEmptyDirectories(\dirname($filePath));
	}

	public function setPublic(File $file, ?Version $version): void
	{
		$filePath = $this->getFilePath($file, $version);

		FileSystem::rename($filePath, $this->getPublicFilePath($file, $version));
		$this->deleteEmptyDirectories(\dirname($filePath));
	}

	public function setPrivate(File $file, ?Version $version): void
	{
		$filePath = $this->getFilePath($file, $version);

		FileSystem::rename($filePath, $this->getPrivateFilePath($file, $version));
		$this->deleteEmptyDirectories(\dirname($filePath));
	}

	public function getStream(File $file, ?Version $version): StreamInterface
	{
		return new Stream(fopen('nette.safe://' . $this->getFilePath($file, $version), 'rb'));
	}

	public function getContentType(File $file, ?Version $version): string
	{
		return (string) \finfo_file(finfo_open(\FILEINFO_MIME_TYPE), $this->getFilePath($file, $version));
	}

	public function getPublicUrl(File $file, ?Version $version): string
	{
		return $this->getFileRelativePath($file, $version);
	}

	public function getSize(File $file, ?Version $version): int
	{
		return filesize($this->getFilePath($file, $version));
	}

	public function exists(File $file, ?Version $version): bool
	{
		return \is_file($this->getPublicFilePath($file, $version)) ||
			\is_file($this->getPrivateFilePath($file, $version));
	}

	public function isPublic(File $file, ?Version $version): bool
	{
		return \is_file($this->getPublicFilePath($file, $version));
	}

	/**
	 * @return \GrandMedia\Files\Version[]
	 */
	public function getVersions(File $file): array
	{
		$versions = [];

		$directories = \array_values(
			\array_filter(
				[
					\dirname($this->getPublicFilePath($file, null)),
					\dirname($this->getPrivateFilePath($file, null)),
				],
				'is_dir'
			)
		);

		if ($directories !== []) {
			/** @var \SplFileInfo $info */
			foreach (Finder::findFiles('*')->in($directories) as $info) {
				if ($info->getBasename() !== $file->getName()) {
					$parts = \explode(self::VERSION_SEPARATOR, $info->getBasename('.' . $info->getExtension()));
					$versions[] = Version::from((string) \end($parts));
				}
			}
		}

		return $versions;
	}

	private function deleteEmptyDirectories(string $directory): void
	{
		$directory = realpath($directory);

		while (
			!\in_array($directory, [$this->privateDirectory, $this->publicDirectory], true) &&
			!(new FilesystemIterator($directory))->valid()
		) {
			FileSystem::delete($directory);

			$parts = \explode('/', $directory);
			\array_pop($parts);
			$directory = $this->joinDirectories($parts);
		}
	}

	private function getFilePath(File $file, ?Version $version): string
	{
		$publicPath = $this->getPublicFilePath($file, $version);
		if (\is_file($publicPath)) {
			return $publicPath;
		}

		$privatePath = $this->getPrivateFilePath($file, $version);
		if (\is_file($privatePath)) {
			return $privatePath;
		}

		throw InvalidFile::notExists($file, $version);
	}

	private function getPrivateFilePath(File $file, ?Version $version): string
	{
		return $this->joinDirectories(
			[
				$this->privateDirectory,
				$this->getFileRelativePath($file, $version),
			]
		);
	}

	private function getPublicFilePath(File $file, ?Version $version): string
	{
		return $this->joinDirectories(
			[
				$this->publicDirectory,
				$this->getFileRelativePath($file, $version),
			]
		);
	}

	private function getFileRelativePath(File $file, ?Version $version): string
	{
		$fileName = $file->getName();
		if ($version !== null) {
			$parts = \explode('.', $fileName);
			$parts[\count($parts) === 1 ? 0 : \count($parts) - 2] .= self::VERSION_SEPARATOR . $version;

			$fileName = \implode('.', $parts);
		}

		return $this->joinDirectories(
			[
				$file->getNamespace(),
				$this->joinDirectories(\str_split($file->getId(), self::ID_CHUNK_LENGTH)),
				$fileName,
			]
		);
	}

	/**
	 * @param string[] $directories
	 */
	private function joinDirectories(array $directories): string
	{
		return \implode('/', $directories);
	}

}
