<?php declare(strict_types = 1);

namespace GrandMediaTests\Files\Responses;

use GrandMedia\Files\Responses\StreamResponse;
use GrandMedia\Files\Utils\StreamFactory;
use GrandMediaTests\Files\Mocks\HttpRequest;
use GrandMediaTests\Files\Mocks\HttpResponse;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class StreamResponseTest extends \Tester\TestCase
{

	private const DATA = 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.';

	public function testSend(): void
	{
		$httpResponse = new HttpResponse();
		$response = new StreamResponse(
			StreamFactory::fromString(self::DATA),
			'test.txt',
			'text/plain',
			\strlen(self::DATA),
			false
		);

		\ob_start();
		$response->send(new HttpRequest(), $httpResponse);
		Assert::same(self::DATA, \ob_get_clean());
		Assert::same('text/plain', $httpResponse->getHeader('Content-Type'));
		Assert::same(\strlen(self::DATA), $httpResponse->getHeader('Content-Length'));
		Assert::same('inline; filename="test.txt"', $httpResponse->getHeader('Content-Disposition'));
	}

	public function testForceDownload(): void
	{
		$httpResponse = new HttpResponse();
		$response = new StreamResponse(
			StreamFactory::fromString(self::DATA),
			'test.txt',
			'text/plain',
			\strlen(self::DATA),
			true
		);

		\ob_start();
		$response->send(new HttpRequest(), $httpResponse);
		Assert::same('attachment; filename="test.txt"', $httpResponse->getHeader('Content-Disposition'));
	}

}

(new StreamResponseTest())->run();
