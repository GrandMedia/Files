<?php declare(strict_types = 1);

namespace GrandMedia\Files;

use GrandMedia\Files\Exceptions\InvalidFile;
use GrandMedia\Files\Exceptions\InvalidStream;
use GrandMedia\Files\Responses\StreamResponse;
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

	public function save(File $file, StreamInterface $stream, bool $rewrite): void
	{
		$this->checkReadableStream($stream);

		if (!$rewrite && $this->exists($file)) {
			throw new InvalidFile('File already exists.');
		}

		$this->storage->save($file, $stream);
	}

	public function delete(File $file): void
	{
		$this->storage->delete($file);
	}

	public function getStreamResponse(File $file, bool $forceDownload): StreamResponse
	{
		return new StreamResponse(
			$this->storage->getStream($file),
			$file->getName(),
			$this->storage->getContentType($file),
			$this->storage->getSize($file),
			$forceDownload
		);
	}

	public function getStream(File $file): StreamInterface
	{
		$stream = $this->storage->getStream($file);

		if ($stream->isWritable()) {
			throw new InvalidStream('Stream cannot be writable.');
		}
		$this->checkReadableStream($stream);

		return $stream;
	}

	public function getContentType(File $file): string
	{
		return $this->storage->getContentType($file);
	}

	public function getPublicUrl(File $file): string
	{
		return $file->isPublic() ? $this->storage->getPublicUrl($file) : '';
	}

	public function getSize(File $file): int
	{
		return $this->storage->getSize($file);
	}

	/**
	 * @return string[]
	 */
	public function getVersions(File $file): array
	{
		return $this->storage->getVersions($file);
	}

	public function exists(File $file): bool
	{
		return $this->storage->exists($file);
	}

	private function checkReadableStream(StreamInterface $stream): void
	{
		if (!$stream->isReadable() || !$stream->isSeekable()) {
			throw new InvalidStream('Stream must be readable and seekable.');
		}
	}

}
