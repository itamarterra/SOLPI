<?php

declare(strict_types=1);

namespace SOLPI\Core\Http;

use RuntimeException;

final class HttpException extends RuntimeException
{
	private int $statusCode;

	private array $response;

	public function __construct(
		string $message,
		int $statusCode = 0,
		array $response = []
	) {

		parent::__construct(

			$message,

			$statusCode

		);

		$this->statusCode = $statusCode;

		$this->response = $response;

	}

	public function statusCode(): int
	{
		return $this->statusCode;
	}

	public function response(): array
	{
		return $this->response;
	}
}

