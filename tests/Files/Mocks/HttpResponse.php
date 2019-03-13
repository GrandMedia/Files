<?php declare(strict_types = 1);

namespace GrandMediaTests\Files\Mocks;

final class HttpResponse implements \Nette\Http\IResponse
{

	/**
	 * @var string[]
	 */
	private $headers = [];

	/**
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @param int $code
	 */
	public function setCode($code): self
	{
		return $this;
	}

	public function getCode(): int
	{
		return 0;
	}

	/**
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @param string $name
	 * @param string $value
	 */
	public function setHeader($name, $value): self
	{
		$this->headers[$name] = $value;

		return $this;
	}

	/**
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @param string $name
	 * @param string $value
	 */
	public function addHeader($name, $value): self
	{
		return $this;
	}

	/**
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @param string $type
	 * @param string|null $charset
	 */
	public function setContentType($type, $charset = null): self
	{
		$this->setHeader('Content-Type', $type);

		return $this;
	}

	/**
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @param string $url
	 * @param int $code
	 */
	public function redirect($url, $code = self::S302_FOUND): void
	{
	}

	/**
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @param int $seconds
	 */
	public function setExpiration($seconds): self
	{
		return $this;
	}

	public function isSent(): bool
	{
		return false;
	}

	/**
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @param string $header
	 * @param string|null $default
	 * @return mixed
	 */
	public function getHeader($header, $default = null)
	{
		return $this->headers[$header];
	}

	/**
	 * @return string[]
	 */
	public function getHeaders(): array
	{
		return $this->headers;
	}

	/**
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @param string $name
	 * @param string $value
	 * @param string $expire
	 * @param string|null $path
	 * @param string|null $domain
	 * @param string|null $secure
	 * @param string|null $httpOnly
	 */
	public function setCookie(
		$name,
		$value,
		$expire,
		$path = null,
		$domain = null,
		$secure = null,
		$httpOnly = null
	): self
	{
		return $this;
	}

	/**
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @param string $name
	 * @param string|null $path
	 * @param string|null $domain
	 * @param string|null $secure
	 */
	public function deleteCookie($name, $path = null, $domain = null, $secure = null): void
	{
	}

}
