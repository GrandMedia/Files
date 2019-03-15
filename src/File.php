<?php declare(strict_types = 1);

namespace GrandMedia\Files;

use Assert\Assertion;

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

	public function __construct(string $id, string $name, string $namespace)
	{
		Assertion::regex($id, self::ID_REGEX);
		Assertion::notBlank($name);
		Assertion::regex($namespace, self::NAMESPACE_REGEX);

		$this->id = $id;
		$this->name = $name;
		$this->namespace = $namespace;
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
