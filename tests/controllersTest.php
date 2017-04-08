<?php

use Silex\WebTestCase;

class controllersTest extends WebTestCase
{
	/**
	 * @param $uri
	 * @param $method
	 * @dataProvider jsonPlaceholderProvider
	 */
	public function testProxyRequest($uri, $method, $code, $content = null)
	{
		$client = $this->createClient();
		$crawler = $client->request($method, $uri, [], [], ['CONTENT_TYPE' => 'application/json'], $content);

		$response = $client->getResponse();
		$this->assertEquals($code, $response->getStatusCode(), $response->getContent());
		$this->assertJson($response->getContent());
	}

	public static function jsonPlaceholderProvider()
	{
		return [
			[
				'uri' => '/posts',
				'method' => \Symfony\Component\HttpFoundation\Request::METHOD_POST,
				'code' => \Symfony\Component\HttpFoundation\Response::HTTP_OK,
				'content' => json_encode([
					"userId" => 1,
					"id" => 1,
					"title" => "Title",
					"body" => "quia et suscipit\nsuscipit recusandae expedita et cum\nreprehenderit molestiae ut ut quas totam\nnostrum rerum est autem sunt rem eveniet architecto"
				]),
			],
			[
				'uri' => '/posts',
				'method' => \Symfony\Component\HttpFoundation\Request::METHOD_GET,
				'code' => \Symfony\Component\HttpFoundation\Response::HTTP_OK,
			],
			[
				'uri' => '/posts/1',
				'method' => \Symfony\Component\HttpFoundation\Request::METHOD_PUT,
				'code' => \Symfony\Component\HttpFoundation\Response::HTTP_OK,
				'content' => json_encode(["title" => "Changed title.",]),
			],
			[
				'uri' => '/posts/1',
				'method' => \Symfony\Component\HttpFoundation\Request::METHOD_GET,
				'code' => \Symfony\Component\HttpFoundation\Response::HTTP_OK,
			],
		];
	}

	public function createApplication()
	{
		$app = require __DIR__ . '/../app/app.php';
		$app['session.test'] = true;

		return $this->app = $app;
	}
}
