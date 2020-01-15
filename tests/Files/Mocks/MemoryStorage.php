<?php declare(strict_types = 1);

namespace GrandMediaTests\Files\Mocks;

use GrandMedia\Files\File;
use GrandMedia\Files\Version;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\StreamInterface;
use Tester\FileMock;
use function Safe\fopen;

final class MemoryStorage implements \GrandMedia\Files\Storage
{

	public const CONTENT_TYPE = 'text/plain';
	public const PUBLIC_URL = 'public/url/';

	/**
	 * @var array[][]
	 */
	private $files;

	/**
	 * @var bool
	 */
	private $returnWritableStream;

	/**
	 * @param string[][] $privateFiles
	 * @param string[][] $publicFiles
	 */
	public function __construct(array $privateFiles = [], array $publicFiles = [], bool $returnWritableStream = false)
	{
		$this->files = [];
		foreach ($privateFiles as $id => $files) {
			foreach ($files as $version => $file) {
				$this->files[$id][$version] = [
					'data' => $file,
					'public' => false,
				];
			}
		}
		foreach ($publicFiles as $id => $files) {
			foreach ($files as $version => $file) {
				$this->files[$id][$version] = [
					'data' => $file,
					'public' => true,
				];
			}
		}
		$this->returnWritableStream = $returnWritableStream;
	}

	public function save(StreamInterface $stream, File $file, ?Version $version, bool $public): void
	{
		$this->files[$file->getId()][(string) $version] = [
			'data' => (string) $stream,
			'public' => $public,
		];
	}

	public function delete(File $file, ?Version $version): void
	{
		unset($this->files[$file->getId()][(string) $version]);
	}

	public function setPublic(File $file, ?Version $version): void
	{
		$this->files[$file->getId()][(string) $version]['public'] = true;
	}

	public function setPrivate(File $file, ?Version $version): void
	{
		$this->files[$file->getId()][(string) $version]['public'] = false;
	}

	public function getStream(File $file, ?Version $version): StreamInterface
	{
		$data = $this->files[$file->getId()][(string) $version]['data'];

		return new Stream(fopen(FileMock::create($data), $this->returnWritableStream ? 'wb' : 'rb'));
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
		return \strlen($this->files[$file->getId()][(string) $version]['data']);
	}

	public function exists(File $file, ?Version $version): bool
	{
		return isset($this->files[$file->getId()][(string) $version]);
	}

	public function isPublic(File $file, ?Version $version): bool
	{
		return $this->files[$file->getId()][(string) $version]['public'];
	}

	/**
	 * @return \GrandMedia\Files\Version[]
	 */
	public function getVersions(File $file): array
	{
		$versions = [];

		foreach (\array_keys($this->files[$file->getId()]) as $version) {
			if ($version !== '') {
				$versions[] = Version::from((string) $version);
			}
		}

		return $versions;
	}

}
