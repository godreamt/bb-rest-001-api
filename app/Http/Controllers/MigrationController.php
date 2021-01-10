<?php

namespace App\Http\Controllers;

use App\User;
use App\Branch;
use App\Company;
use App\TableManager;
use App\BranchKitchen;
use GuzzleHttp\Client;
use App\BranchOrderType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MigrationController extends Controller
{
    /**
     * Gets the local unsychronised data and sends it to synchronisation server for synching
     */
    public function syncStart(Request $request) {
        $listModels = [
            'companies' => Company::select('*'),
            'branches' => Branch::select('*'),
            'branch_order_types' => BranchOrderType::select('*'),
            'branch_kitchens' => BranchKitchen::select('*'),
            'table_managers' => TableManager::select('*'),
            'users' => User::select('*'),
        ];

        $result = [];
        foreach($listModels as $key => $model) {
            $result[$key] = $this->getUnSyncData($model);
        }
        $client = new Client();

        $res = Http::post('https://connect.runrestro.com/api/execute', [
            'data' => $result
        
        ]);
        $res->throw();
        return $res;
        // $response_data=[];
        // if ($res->getStatusCode() == 200) { // 200 OK
        //      $response_data = $res->getBody()->getContents();
        // }
        // // return $res->getBody();
        // return $response_data;
    }

    public function getUnSyncData($model) {
        return $model->where('isSync', false)->get();
    }

    public function syncExecute(Request $request) {
        $data = $request->data;
        $result = [];
        foreach($data as $model) {
            foreach($model as $item) {
                $result[] = $item['id'];
            }
        }
        return $result;
    }
}


// As of Laravel v7.X, the framework now comes with a minimal API wrapped around the Guzzle HTTP client. It provides an easy way to make get, post, put, patch, and delete requests using the HTTP Client:

// use Illuminate\Support\Facades\Http;

// $response = Http::get('http://test.com');
// $response = Http::post('http://test.com');
// $response = Http::put('http://test.com');
// $response = Http::patch('http://test.com');
// $response = Http::delete('http://test.com');
// You can manage responses using the set of methods provided by the Illuminate\Http\Client\Response instance returned.

// $response->body() : string;
// $response->json() : array;
// $response->status() : int;
// $response->ok() : bool;
// $response->successful() : bool;
// $response->serverError() : bool;
// $response->clientError() : bool;
// $response->header($header) : string;
// $response->headers() : array;
// Please note that you will, of course, need to install Guzzle like so:

// composer require guzzlehttp/guzzle
// There are a lot more helpful features built-in and you can find out more about these set of the feature here: https://laravel.com/docs/7.x/http-client

// This is definitely now the easiest way to make external API calls within Laravel.