<?php declare(strict_types = 1);

namespace GrandMedia\Files;

use Assert\Assertion;
use Nette\Utils\Random;

final class File
{

	private const ID_REGEX = '/^[a-zA-Z\d-_]+$/';
	private const NAMESPACE_REGEX = '/^[a-zA-Z\d-_\/]+$/';

	/**
	 * @var string
	 */
	private $id;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $namespace;

	private function __construct()
	{
	}

	public static function fromValues(string $id, string $name, string $namespace): self
	{
		Assertion::regex($id, self::ID_REGEX);
		Assertion::notBlank($name);
		Assertion::regex($namespace, self::NAMESPACE_REGEX);

		$file = new self();
		$file->id = $id;
		$file->name = $name;
		$file->namespace = $namespace;

		return $file;
	}

	public static function withRandomId(string $name, string $namespace): self
	{
		return self::fromValues(Random::generate(), $name, $namespace);
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getNamespace(): string
	{
		return $this->namespace;
	}

}
