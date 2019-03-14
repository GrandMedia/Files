<?php declare(strict_types = 1);

namespace GrandMedia\Files;

use GrandMedia\Files\Exceptions\InvalidFile;
use GrandMedia\Files\Exceptions\InvalidStream;
use GuzzleHttp\Stream\StreamInterface;

final class FilesManager
{

	/**
	 * @var \GrandMedia\Files\Storage
	 */
	private $storage;

	public function __construct(Storage $storage)
	{
		$this->storage = $storage;
	}

	public function save(StreamInterface $stream, bool $rewrite, File $file, ?Version $version = null): void
	{
		$this->checkReadableStream($stream);

		if (!$rewrite && $this->exists($file, $version)) {
			throw new InvalidFile('File already exists.');
		}

		$this->storage->save($stream, $file, $version);
	}

	public function delete(File $file): void
	{
		if ($this->exists($file)) {
			$this->storage->delete($file, null);
		}

		foreach ($this->storage->getVersions($file) as $version) {
			$this->storage->delete($file, $version);
		}
	}

	public function deleteVersion(File $file, ?Version $version = null): void
	{
		$this->checkExists($file, $version);

		$this->storage->delete($file, $version);
	}

	public function getStream(File $file, ?Version $version = null): StreamInterface
	{
		$this->checkExists($file, $version);

		$stream = $this->storage->getStream($file, $version);

		if ($stream->isWritable()) {
			throw new InvalidStream('Stream cannot be writable.');
		}
		$this->checkReadableStream($stream);

		return $stream;
	}

	public function getContentType(File $file, ?Version $version = null): string
	{
		$this->checkExists($file, $version);

		return $this->storage->getContentType($file, $version);
	}

	public function getPublicUrl(File $file, ?Version $version = null): string
	{
		$this->checkExists($file, $version);

		return $file->isPublic() ? $this->storage->getPublicUrl($file, $version) : '';
	}

	public function getSize(File $file, ?Version $version = null): int
	{
		$this->checkExists($file, $version);

		return $this->storage->getSize($file, $version);
	}

	public function exists(File $file, ?Version $version = null): bool
	{
		return $this->storage->exists($file, $version);
	}

	/**
	 * @return \GrandMedia\Files\Version[]
	 */
	public function getVersions(File $file): array
	{
		return $this->storage->getVersions($file);
	}

	private function checkReadableStream(StreamInterface $stream): void
	{
		if (!$stream->isReadable() || !$stream->isSeekable()) {
			throw new InvalidStream('Stream must be readable and seekable.');
		}
	}

	private function checkExists(File $file, ?Version $version): void
	{
		if (!$this->exists($file, $version)) {
			throw new InvalidFile('File does not exists.');
		}
	}

}
