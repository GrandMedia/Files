<?php declare(strict_types = 1);

namespace GrandMediaTests\Files\Mocks;

use Nette\Http\UrlScript;

final class HttpRequest implements \Nette\Http\IRequest
{

	public function getUrl(): UrlScript
	{
		return new UrlScript();
	}

	/**
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @param string|null $key
	 * @param string|null $default
	 */
	public function getQuery($key = null, $default = null): string
	{
		return '';
	}

	/**
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @param string|null $key
	 * @param string|null $default
	 */
	public function getPost($key = null, $default = null): string
	{
		return '';
	}

	/**
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @param string $key
	 */
	public function getFile($key): string
	{
		return '';
	}

	public function getFiles(): string
	{
		return '';
	}

	/**
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @param string $key
	 * @param string|null $default
	 */
	public function getCookie($key, $default = null): string
	{
		return '';
	}

	public function getCookies(): string
	{
		return '';
	}

	public function getMethod(): string
	{
		return '';
	}

	/**
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @param string $method
	 */
	public function isMethod($method): bool
	{
		return false;
	}

	/**
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @param string $header
	 * @param string|null $default
	 */
	public function getHeader($header, $default = null): string
	{
		return '';
	}

	public function getHeaders(): string
	{
		return '';
	}

	public function isSecured(): bool
	{
		return false;
	}

	public function isAjax(): bool
	{
		return false;
	}

	public function getRemoteAddress(): string
	{
		return '';
	}

	public function getRemoteHost(): string
	{
		return '';
	}

	public function getRawBody(): string
	{
		return '';
	}

}
