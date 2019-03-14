<?php declare(strict_types = 1);

namespace GrandMedia\Files;

use GuzzleHttp\Stream\StreamInterface;

interface Storage
{

	public function save(StreamInterface $stream, File $file, ?Version $version): void;

	public function delete(File $file, ?Version $version): void;

	public function getStream(File $file, ?Version $version): StreamInterface;

	public function getContentType(File $file, ?Version $version): string;

	public function getPublicUrl(File $file, ?Version $version): string;

	public function getSize(File $file, ?Version $version): int;

	public function exists(File $file, ?Version $version): bool;

	/**
	 * @return \GrandMedia\Files\Version[]
	 */
	public function getVersions(File $file): array;

}
