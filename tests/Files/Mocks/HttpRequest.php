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
	 * @return \Nette\Http\FileUpload|array|null
	 */
	public function getFile($key)
	{
		return null;
	}

	public function getFiles(): array
	{
		return [];
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

	public function getCookies(): array
	{
		return [];
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

	public function getHeaders(): array
	{
		return [];
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
