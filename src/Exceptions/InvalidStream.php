<?php declare(strict_types = 1);

namespace GrandMedia\Files\Exceptions;

final class InvalidStream extends \InvalidArgumentException
{

	public static function notReadableOrSeekable(): self
	{
		return new self('Stream must be readable and seekable.');
	}

	public static function writable(): self
	{
		return new self('Stream cannot be writable.');
	}

}
