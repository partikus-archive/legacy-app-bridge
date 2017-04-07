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
		$client->followRedirects(true);
		$crawler = $client->request($method, $uri, [], [], [
			'CONTENT_TYPE' => 'application/json',
		], $content);

		$this->assertEquals($code, $client->getResponse()->getStatusCode());
		$this->assertJson($client->getResponse()->getContent());
	}

	public static function jsonPlaceholderProvider()
	{
		return [
			[
				'uri' => '/api/users',
				'method' => \Symfony\Component\HttpFoundation\Request::METHOD_GET,
				'code' => \Symfony\Component\HttpFoundation\Response::HTTP_OK,
			],
			[
				'uri' => '/api/users',
				'method' => \Symfony\Component\HttpFoundation\Request::METHOD_POST,
				'code' => \Symfony\Component\HttpFoundation\Response::HTTP_CREATED,
				'content' => json_encode([
					"name" => "morpheus",
					"job" => "leader"
				]),
			],
			[
				'uri' => '/api/users',
				'method' => \Symfony\Component\HttpFoundation\Request::METHOD_PUT,
				'code' => \Symfony\Component\HttpFoundation\Response::HTTP_OK,
				'content' => json_encode(["job" => "leader"]),
			],
			[
				'uri' => '/api/users/1',
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
