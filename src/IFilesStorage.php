<?php declare(strict_types = 1);

namespace GrandMedia\Files;

use GuzzleHttp\Stream\StreamInterface;

interface IFilesStorage
{

	public function save(File $file, StreamInterface $stream): void;

	public function delete(File $file): void;

	public function getStream(File $file): StreamInterface;

	public function getContentType(File $file): string;

	public function getPublicUrl(File $file): string;

	public function getSize(File $file): int;

	/**
	 * @return string[]
	 */
	public function getVersions(File $file): array;

	public function exists(File $file): bool;

}
