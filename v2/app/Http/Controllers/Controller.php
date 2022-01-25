<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	public function sendResponse($result,$message){
		$response = [
			'ok' => true,
			'data'=>$result,
			'message'=> $message,
		];
		return response()->json($response,200);
	}

	public function sendError($error, $errorMessages = [], $code = 404){
		$response = [
			'ok' => false,
			'data'=>$errorMessages,
			'message'=> $error,
		];
		return response()->json($response,code);

	}
}