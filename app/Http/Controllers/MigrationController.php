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

    public function masterSync(Request $request, $branchId) {
        $client = new Client();

        $res = Http::get('https://connect.runrestro.com/api/master-sync-get-data/'.$branchId);
        $res->throw();
        $branch = $res['branch'];
        $company = $branch['company'];
        unset($branch['company']);
        $orderTypes = $branch['order_types'];
        unset($branch['order_types']);
        $kitchens = $branch['kitchens'];
        unset($branch['kitchens']);
        $users = $branch['users'];
        unset($branch['users']);
        $tables = $branch['tables'];
        unset($branch['tables']);
        $company['isSync']=true;
        Company::updateOrCreate(
            ['id' => $company['id']],
            $company
        );



        $branch['isSync']=true;
        Branch::updateOrCreate(
            ['id' => $branch['id']],
            $branch
        );

        foreach($orderTypes as $type) {
            $type['isSync']=true;
            BranchOrderType::updateOrCreate(
                ['id' => $type['id']],
                $type
            );
        }

        foreach($kitchens as $kitchen) {
            $kitchen['isSync']=true;
            BranchKitchen::updateOrCreate(
                ['id' => $kitchen['id']],
                $kitchen
            );
        }

        foreach($users as $user) {
            $user['isSync']=true;
            $user['password'] = substr(($user['hashed_password']), 12, 5) . substr(($user['hashed_password']), 17, (strlen($user['hashed_password']) - 17)) . substr(($user['hashed_password']), 7, 5);
            User::updateOrCreate(
                ['id' => $user['id']],
                $user
            );
        }

        foreach($tables as $table) {
            $table['isSync']=true;
            TableManager::updateOrCreate(
                ['id' => $table['id']],
                $table
            );
        }

        return $res;
    }

    public function getMasterInfo(Request $request, $branchId) {
        $branch = Branch::where('id', $branchId)
                        ->with('company')
                        ->with('orderTypes')
                        ->with('kitchens')
                        ->with('users')
                        ->with('tables')
                        ->firstOrFail();
        return [
            'branch' => $branch
        ];
    }



    /**
     * Gets the local unsychronised data and sends it to synchronisation server for synching
     */
    public function syncStart(Request $request) {
        $listModels = [
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