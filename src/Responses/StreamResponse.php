<?php declare(strict_types = 1);

namespace GrandMedia\Files\Responses;

use GuzzleHttp\Stream\StreamInterface;
use Nette\Http\IRequest;
use Nette\Http\IResponse;

final class StreamResponse implements \Nette\Application\IResponse
{

	/**
	 * @var \GuzzleHttp\Stream\StreamInterface
	 */
	private $stream;

	/**
	 * @var string
	 */
	private $contentType;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var int
	 */
	private $size;

	/**
	 * @var bool
	 */
	private $forceDownload;

	public function __construct(
		StreamInterface $stream,
		string $name,
		string $contentType,
		int $size,
		bool $forceDownload
	)
	{
		$this->stream = $stream;
		$this->name = $name;
		$this->contentType = $contentType;
		$this->size = $size;
		$this->forceDownload = $forceDownload;
	}

	public function send(IRequest $httpRequest, IResponse $httpResponse): void
	{
		$httpResponse->setContentType($this->contentType);
		$httpResponse->setHeader('Content-Length', $this->size);
		$httpResponse->setHeader(
			'Content-Disposition',
			($this->forceDownload ? 'attachment' : 'inline')
			. '; filename="' . $this->name . '"'
		);

		while (!$this->stream->eof()) {
			echo $this->stream->read((int) 4e6);
		}
	}

}
