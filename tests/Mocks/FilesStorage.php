<?php declare(strict_types = 1);

namespace GrandMediaTests\Files\Mocks;

use GrandMedia\Files\File;
use GrandMedia\Files\Utils\StreamFactory;
use GuzzleHttp\Stream\StreamInterface;
use Nette\Utils\Strings;
use Tester\FileMock;

final class FilesStorage implements \GrandMedia\Files\IFilesStorage
{

	public const CONTENT_TYPE = 'text/plain';
	public const PUBLIC_URL = 'public/url/';

	/** @var string[][] */
	private $files = [];

	/** @var bool */
	private $returnWritableStream;

	/** @param  string[][] $files */
	public function __construct(array $files = [], bool $returnWritableStream = false)
	{
		$this->files = $files;
		$this->returnWritableStream = $returnWritableStream;
	}

	public function save(File $file, StreamInterface $stream): void
	{
		$this->files[$file->getId()][$file->getVersion()] = (string) $stream;
	}

	public function delete(File $file): void
	{
		unset($this->files[$file->getId()][$file->getVersion()]);
	}

	public function getStream(File $file): StreamInterface
	{
		$data = $this->files[$file->getId()][$file->getVersion()];

		return $this->returnWritableStream ?
			StreamFactory::fromString($data) :
			StreamFactory::fromPath(FileMock::create($data));
	}

	public function getContentType(File $file): string
	{
		return self::CONTENT_TYPE;
	}

	public function getPublicUrl(File $file): string
	{
		return self::PUBLIC_URL . $file->getId();
	}

	public function getSize(File $file): int
	{
		return \strlen($this->files[$file->getId()][$file->getVersion()]);
	}

	/**
	 * @return string[]
	 */
	public function getVersions(File $file): array
	{
		$versions = [];

		foreach ($this->files[$file->getId()] as $version => $data) {
			if (Strings::startsWith($version, $file->getVersion())) {
				$versions[] = $version;
			}
		}

		return $versions;
	}

	public function exists(File $file): bool
	{
		return isset($this->files[$file->getId()][$file->getVersion()]);
	}

}