<?php declare(strict_types = 1);

namespace GrandMediaTests\Files\Mocks;

use GrandMedia\Files\File;
use GrandMedia\Files\Version;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Stream\StreamInterface;
use Tester\FileMock;
use function Safe\fopen;

final class MemoryStorage implements \GrandMedia\Files\Storage
{

	public const CONTENT_TYPE = 'text/plain';
	public const PUBLIC_URL = 'public/url/';

	/**
	 * @var string[][]
	 */
	private $files;

	/**
	 * @var string[][]
	 */
	private $publicFiles;

	/**
	 * @var bool
	 */
	private $returnWritableStream;

	/**
	 * @param  string[][] $files
	 * @param  string[][] $publicFiles
	 */
	public function __construct(array $files = [], array $publicFiles = [], bool $returnWritableStream = false)
	{
		$this->files = $files;
		$this->publicFiles = $publicFiles;
		$this->returnWritableStream = $returnWritableStream;
	}

	public function save(StreamInterface $stream, File $file, ?Version $version): void
	{
		$this->files[$file->getId()][$version === null ? '' : (string) $version] = (string) $stream;
	}

	public function delete(File $file, ?Version $version): void
	{
		unset(
			$this->files[$file->getId()][$version === null ? '' : (string) $version],
			$this->publicFiles[$file->getId()][$version === null ? '' : (string) $version]
		);
	}

	public function setPublic(File $file, ?Version $version): void
	{
		$this->publicFiles[$file->getId()][$version === null ? '' : (string) $version] = true;
	}

	public function setPrivate(File $file, ?Version $version): void
	{
		unset($this->publicFiles[$file->getId()][$version === null ? '' : (string) $version]);
	}

	public function getStream(File $file, ?Version $version): StreamInterface
	{
		$data = $this->files[$file->getId()][$version === null ? '' : (string) $version];

		return $this->returnWritableStream ?
			Stream::factory($data) :
			Stream::factory(fopen(FileMock::create($data), 'rb'));
	}

	public function getContentType(File $file, ?Version $version): string
	{
		return self::CONTENT_TYPE;
	}

	public function getPublicUrl(File $file, ?Version $version): string
	{
		return self::PUBLIC_URL . $file->getId() . ($version === null ? '' : '_' . $version);
	}

	public function getSize(File $file, ?Version $version): int
	{
		return \strlen($this->files[$file->getId()][$version === null ? '' : (string) $version]);
	}

	public function exists(File $file, ?Version $version): bool
	{
		return isset($this->files[$file->getId()][$version === null ? '' : (string) $version]);
	}

	public function isPublic(File $file, ?Version $version): bool
	{
		return isset($this->publicFiles[$file->getId()][$version === null ? '' : (string) $version]);
	}

	/**
	 * @return \GrandMedia\Files\Version[]
	 */
	public function getVersions(File $file): array
	{
		$versions = [];

		if (isset($this->files[$file->getId()])) {
			foreach ($this->files[$file->getId()] as $version => $data) {
				if ($version !== '') {
					$versions[] = Version::from((string) $version);
				}
			}
		}

		return $versions;
	}

}
