<?php

use Symfony\Component\HttpFoundation\Request;

$app = new Silex\Application();

$app->get('/', function () use ($app) {
	return $app->json(['Works fine!']);
})->bind('homepage');

$app->match('/{path}', function (Request $request) use ($app) {
//	Don't treat below code as a production one just a code snippet including simple usage example.
//	In order to send proper request we have to remove useless headers.
	$excludedHeaders = ['Host', 'Transfer-Encoding', 'X-Proxy-URL'];
//	Remove symfony/silex request attributes.
	$excludedAttributes = ['_controller', 'path', '_route', '_route_params'];

//	Guzzle
	$guzzleClientOptions = [
		\GuzzleHttp\RequestOptions::DECODE_CONTENT => false,
		\GuzzleHttp\RequestOptions::TIMEOUT => 3,
		\GuzzleHttp\RequestOptions::ALLOW_REDIRECTS => false,
		\GuzzleHttp\RequestOptions::VERIFY => true,
		\GuzzleHttp\RequestOptions::HTTP_ERRORS => true,
		\GuzzleHttp\RequestOptions::COOKIES => new \GuzzleHttp\Cookie\CookieJar(),
		\GuzzleHttp\RequestOptions::VERSION => 6,
	];

	$client = new \GuzzleHttp\Client(['base_uri' => 'https://jsonplaceholder.typicode.com']);
	$requestFactory = new \Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory();
	$httpFactory = new \Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory();


	/** @var \Psr\Http\Message\ServerRequestInterface $serverRequest */
//	Converting Symfony Request to PSR
	$serverRequest = $requestFactory->createRequest($request);

//	In order to send request to host defined in the client object and keep default ones
//	we have to clean uri attributes like host and schema
	$uri = $serverRequest->getUri()
		->withHost("")
		->withScheme("");

	$serverRequest = $serverRequest->withUri($uri);

//	Removing excluded headers described above
	foreach ($excludedHeaders as $excludedHeader) {
		$serverRequest = $serverRequest->withoutHeader($excludedHeader);
	}

	$contentType = $serverRequest->getHeader('Content-Type');
	if (null !== $contentType && preg_match('/multipart/', $contentType[0])) {
		$serverRequest = $serverRequest->withoutHeader('Content-Type');
	}

//	Removing excluded attributes described above
	foreach ($excludedAttributes as $excludedAttribute) {
		$serverRequest = $serverRequest->withoutAttribute($excludedAttribute);
	}

//	In order to send multipart form
	foreach ($serverRequest->getUploadedFiles() as $name => $uploadedFile) {
		$guzzleClientOptions['multipart'][] = [
			'name' => $name,
			'contents' => $uploadedFile->getStream(),
			'headers' => ['Content-Type' => 'multipart/form-data'],
		];
	}

	try {
		$response = $client->send($serverRequest, $guzzleClientOptions);
		$response
			->withHeader('X-Proxy-Location', $response->getHeader('Location'))
			->withoutHeader('Location')
			->withoutHeader('Transfer-Encoding');

		return $httpFactory->createResponse($response);
	} catch (\Exception $exception) {
		return $app->json(
			[
				'message' => $exception->getMessage(), 
				'response' => (array) $exception->getResponse(),
			], 
			\Symfony\Component\HttpFoundation\Response::HTTP_INTERNAL_SERVER_ERROR
		);
	}
})->assert('path', '.*');

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
	if ($app['debug']) {
		return;
	}
	return $app->json(['code' => $code, 'message' => $e->getMessage()], $code);
});


return $app;
