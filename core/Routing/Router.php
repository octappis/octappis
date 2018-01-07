<?php

class Router
{


	public static function direct($routes)
	{

		$method = Request::method();

		$uri = Request::uri();

		$results = false;
		
		foreach ($routes as $route) {

			if ($route[0] == $method && self::checkUri($route[1]) ) {

				$controllerPath = "../app/Controllers/$route[5]Controller.php";

				$controllerName = $route[2]."Controller";

				$methodName = $route[3];

				$results = true;

				break;
			}
		}

		if ($results) {

			require $controllerPath;

			$response = new Response ;
			$request = new Request;

			$test = new $controllerName();

			$test->$methodName($request, $response);

		}else{

			$app = require '/../../config/app.php';

			if($app['404_page'] == 'init'){

				return view('404');

			}else{

				return view($app['404_page']);

			}
			
			
		}
		
	}

	public static function checkUri($get_uri)
	{
		preg_match_all("/{(.*?)}/", $get_uri , $results);
		$uri_params = $results[0];


		$uri_paths = explode('/', $get_uri);


		$request_uri_path = explode('/', Request::uri());



		$position = [];
		$unposition = [];
		$optional = false;
		$counter = 0;
		foreach ($uri_paths as $path) {
			$change = false;
			foreach ($uri_params as $param) {
				if($path == $param){
					$position[] = $counter; 
					$change = true;
				}
			}

			if(!$change){
				$unposition[] = $counter;
			}
			$counter ++ ;
		}

		$counter = 0;
		$success_1 = 0;
		$success_2 = 0;
		foreach ($uri_paths as $path) {
			
			if(in_array($counter, $unposition)){
				if (isset($request_uri_path[$counter])) {
					if ($path == $request_uri_path[$counter]) {
						$success_1 ++;
					}
				}
					
			}elseif ($path[0] =="{" && isset($request_uri_path[$counter])) {
				$success_2 ++;
			}elseif ($path[1] =="#") {
				$optional = true;
			}

			$counter ++;
		}
		$num1 = count(array_merge($position,$unposition));
		$num2 = count($request_uri_path);
		if (($success_1 +$success_2  == $num1  && $success_1 +$success_2 == $num2) ||
			($success_1 +$success_2  == $num1-1  && $success_1 +$success_2 == $num2 && $optional )){
			return true;
		}else{
			return false;
		}
	}

}