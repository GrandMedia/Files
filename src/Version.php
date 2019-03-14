<?php declare(strict_types = 1);

namespace GrandMedia\Files;

use Assert\Assertion;

final class Version
{

	private const VALUE_REGEX = '/^[a-zA-Z\d-]+$/';

	/**
	 * @var string
	 */
	private $value;

	private function __construct()
	{
	}

	public static function from(string $value): self
	{
		Assertion::regex($value, self::VALUE_REGEX);

		$version = new self();
		$version->value = $value;

		return $version;
	}

	public function __toString(): string
	{
		return $this->value;
	}

}
