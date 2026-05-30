<?php 
namespace SlimApp\Controllers;

use SlimApp\Enums\HTTPStatus;
use Psr\Http\Message\ResponseFactoryInterface as ResponseFactory;
use Slim\Interfaces\RouteCollectorProxyInterface as RouteCollector;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\PhpRenderer;

/**
 * Base controller providing common response and request handling utilities
 */
abstract class BaseController {

	/**
	 * Response factory used to create HTTP responses
	 */
	protected ResponseFactory $respFact;
	protected PhpRenderer $renderer;

	/**
	 * Creates a controller instance with a response factory
	 *
	 * @param ResponseFactory $respFact
	 */
	public function __construct(ResponseFactory $respFact, PhpRenderer $renderer) {
		$this->respFact = $respFact;
		$this->renderer = $renderer;
	}

	/**
	 * Allows to send a JSON response with a given HTTP status
	 *
	 * @param mixed $data
	 * @param HTTPStatus $status
	 * @return Response
	 */
	protected function sendJSON(mixed $data, HTTPStatus $status = HTTPStatus::OK): Response {
		$resp = $this->respFact->createResponse($status->value);
		$body = $resp->getBody();
		$body->write(json_encode($data));

		return $resp->withHeader('Content-Type', 'application/json');
	}

	/**
	 * Allows to retrieve and decode the JSON body of a request
	 *
	 * @param Request $request
	 * @return array
	 */
	protected function getBody(Request $request): array {
		$body = json_decode($request->getBody()->getContents(), true);

		if ($body === null)
			$body = [];

		return $body;
	}

	/**
	 * Allows to send a JSON response containing validation or processing errors
	 *
	 * @param array $errors
	 * @param HTTPStatus $status
	 * @return Response
	 */
	protected function sendErrors(array $errors, HTTPStatus $status = HTTPStatus::BAD_REQUEST): Response {
		return $this->sendJSON(["errors" => $errors], $status);
	}

	protected function render(string $page, array $data = []): Response {
		$resp = $this->respFact->createResponse();
		return $this->renderer->render($resp, $page, $data);
	}

	protected function redirect(string $url, HTTPStatus $status = HTTPStatus::MOVED_PERMANENTLY, ?Response $resp = null): Response {
		if ($resp === null)
			$resp = $this->respFact->createResponse($status->value); 
		return $resp->withStatus($status->value)->withHeader("Location", $url);
	}
}
