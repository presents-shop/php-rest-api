<?php

class Router
{
	private $routes = [];

	private function registerRoute($method, $uri, $controller)
	{
		$this->routes[] = [
			"method" => $method,
			"uri" => $uri,
			"controller" => $controller,
		];
	}

	public function get($uri, $controller)
	{
		$this->registerRoute("GET", $uri, $controller);
	}

	public function post($uri, $controller)
	{
		$this->registerRoute("POST", $uri, $controller);
	}

	public function put($uri, $controller)
	{
		$this->registerRoute("PUT", $uri, $controller);
	}

	public function delete($uri, $controller)
	{
		$this->registerRoute("DELETE", $uri, $controller);
	}

	public function route($uri, $method)
	{
		$parsedUrl = parse_url($uri);
		$path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
		$query = isset($parsedUrl['query']) ? $parsedUrl['query'] : '';

		// Парсиране на query параметрите и добавяне към $_GET
		parse_str($query, $query_params);
		$_GET = array_merge($_GET, $query_params);

		foreach ($this->routes as $route) {
			if ($route["method"] === $method && $this->isUriMatch($route["uri"], $path)) {
				list($controllerClass, $controllerMethod) = $route["controller"];

				if (class_exists($controllerClass) && method_exists($controllerClass, $controllerMethod)) {
					$controller = new $controllerClass();
					$controller->$controllerMethod();
				} else {
					http_response_code(404);
					ErrorController::notFound();
				}
				return;
			}
		}

		http_response_code(404);
		ErrorController::notFound();
		exit;
	}

	private function isUriMatch($routeUri, $requestUri)
	{
		$routeParts = explode("/", trim($routeUri, "/"));
		$requestParts = explode("/", trim($requestUri, "/"));

		if (count($routeParts) !== count($requestParts)) {
			return false;
		}

		$params = [];

		foreach ($routeParts as $index => $routePart) {
			if ($routePart !== $requestParts[$index]) {
				if (strpos($routePart, ":") === 0) {
					// Този елемент е динамичен параметър
					$paramName = substr($routePart, 1);
					$paramValue = $requestParts[$index];
					$params[$paramName] = $paramValue;
				} else {
					// Статичните части трябва да съвпадат точно
					return false;
				}
			}
		}

		// Прехвърляне на параметрите в $_GET
		$_GET = array_merge($_GET, $params);

		return true;
	}
}