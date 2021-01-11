<?php

namespace App\Http\Controllers;

use App\User;
use App\Order;
use App\Branch;
use App\Company;
use App\Product;
use App\Category;
use App\Customer;
use App\OrderItem;
use App\OrderTable;
use App\TableManager;
use App\BranchKitchen;
use GuzzleHttp\Client;
use App\UserAttendance;
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
        try {
            $token = $request->header('Authorization');
            $branchId = \Auth::user()->branch_id;
            // $listModels = [
            //     'users' => User::select('*'),
            //     'user_attendances' => UserAttendance::select('*'),
            //     'categories' => Category::select('*'),
            //     'products' => Product::select('*'),
            //     'table_managers' => TableManager::select('*'),
            //     'orders' => Order::select('*'),
            //     'order_items' => OrderItem::select('*'),
            //     'order_tables' => OrderTable::select('*')
            // ];

            $result = [];

            // Users
            $result['users'] = User::where('branch_id', $branchId)->where('users.isSync', false)->get();
            $result['user_attendances'] = UserAttendance::select('user_attendances.*')->leftJoin('users', 'users.id', 'user_attendances.user_id')->where('users.branch_id', $branchId)->where('user_attendances.isSync', false)->get();
            $result['categories'] = Category::where('branch_id', $branchId)->where('isSync', false)->get();
            $result['products'] = Product::where('branch_id', $branchId)->where('isSync', false)->get();
            $result['table_managers'] = TableManager::where('branch_id', $branchId)->where('isSync', false)->get();
            $result['customers'] = Customer::where('branch_id', $branchId)->where('isSync', false)->get();
            $result['orders'] = Order::where('branch_id', $branchId)->where('isSync', false)->get();
            $result['order_items'] = OrderItem::select('order_items.*')->leftJoin('orders', 'orders.id', 'order_items.orderId')->where('orders.branch_id', $branchId)->where('order_items.isSync', false)->get();
            $result['order_tables'] = OrderTable::select('order_tables.*')->leftJoin('orders', 'orders.id', 'order_tables.orderId')->where('orders.branch_id', $branchId)->where('order_tables.isSync', false)->get();

            $client = new Client();

            $res = Http::withHeaders([
                'Authorization' => $token
            ])->post('https://connect.runrestro.com/api/execute', [
                'data' => $result,
                'branchId' => $branchId
            ]);
            $res->throw();
            if($res['success']) {
                // online response handling
                
                $onlineResponse = [];
    
                // user reset the is sync to true
                
                $onlineResponse['users'] = [];
                foreach($res['users']['syncDone'] as $userId) {
                    $user = User::find($userId);
                    $user->isSync = true;
                    $user->save();
                }
    
                // users sync from online data
                foreach($res['users']['onlineRecords'] as $user) {
                    $user['isSync'] = true;
                    $onlineResponse['users'][] = $user['id'];
                    $user['password'] = substr(($user['hashed_password']), 12, 5) . substr(($user['hashed_password']), 17, (strlen($user['hashed_password']) - 17)) . substr(($user['hashed_password']), 7, 5);
                    User::updateOrCreate(
                        ['id' => $user['id']],
                        $user
                    );
                }
    
                // user attendance
                $onlineResponse['user_attendances'] = [];
                foreach($res['user_attendances']['syncDone'] as $userId) {
                    $attendance = UserAttendance::find($userId);
                    $attendance->isSync = true;
                    $attendance->save();
                }
                foreach($res['user_attendances']['onlineRecords'] as $attendance) {
                    $attendance['isSync'] = true;
                    $onlineResponse['user_attendances'][] = $attendance['id'];
                    UserAttendance::updateOrCreate(
                        ['id' => $attendance['id']],
                        $attendance
                    );
                }
    
                // categories
                $onlineResponse['categories'] = [];
                foreach($res['categories']['syncDone'] as $itemId) {
                    $item = Category::find($itemId);
                    $item->isSync = true;
                    $item->save();
                }
                foreach($res['categories']['onlineRecords'] as $item) {
                    $item['isSync'] = true;
                    $onlineResponse['categories'][] = $item['id'];
                    Category::updateOrCreate(
                        ['id' => $item['id']],
                        $item
                    );
                }
    
                // products
                $onlineResponse['products'] = [];
                foreach($res['products']['syncDone'] as $itemId) {
                    $item = Product::find($itemId);
                    $item->isSync = true;
                    $item->save();
                }
                foreach($res['products']['onlineRecords'] as $item) {
                    $item['isSync'] = true;
                    $onlineResponse['products'][] = $item['id'];
                    Product::updateOrCreate(
                        ['id' => $item['id']],
                        $item
                    );
                }
    
                // table_managers
    
                $onlineResponse['table_managers'] = [];
                foreach($res['table_managers']['syncDone'] as $itemId) {
                    $item = TableManager::find($itemId);
                    $item->isSync = true;
                    $item->save();
                }
                foreach($res['table_managers']['onlineRecords'] as $item) {
                    $item['isSync'] = true;
                    $onlineResponse['table_managers'][] = $item['id'];
                    TableManager::updateOrCreate(
                        ['id' => $item['id']],
                        $item
                    );
                }
    
                // customers
    
                $onlineResponse['customers'] = [];
                foreach($res['customers']['syncDone'] as $itemId) {
                    $item = Customer::find($itemId);
                    $item->isSync = true;
                    $item->save();
                }
                foreach($res['customers']['onlineRecords'] as $item) {
                    $item['isSync'] = true;
                    $onlineResponse['customers'][] = $item['id'];
                    Customer::updateOrCreate(
                        ['id' => $item['id']],
                        $item
                    );
                }
    
                // orders
                $onlineResponse['orders'] = [];
                foreach($res['orders']['syncDone'] as $itemId) {
                    $item = Order::find($itemId);
                    $item->isSync = true;
                    $item->save();
                }
                foreach($res['orders']['onlineRecords'] as $item) {
                    $item['isSync'] = true;
                    $onlineResponse['orders'][] = $item['id'];
                    Order::updateOrCreate(
                        ['id' => $item['id']],
                        $item
                    );
                }
    
                // order_items
                $onlineResponse['order_items'] = [];
                foreach($res['order_items']['syncDone'] as $itemId) {
                    $item = OrderItem::find($itemId);
                    $item->isSync = true;
                    $item->save();
                }
                foreach($res['order_items']['onlineRecords'] as $item) {
                    $item['isSync'] = true;
                    $onlineResponse['order_items'][] = $item['id'];
                    OrderItem::updateOrCreate(
                        ['id' => $item['id']],
                        $item
                    );
                }
    
                // orders tables
                $onlineResponse['order_tables'] = [];
                foreach($res['order_tables']['syncDone'] as $itemId) {
                    $item = OrderTable::find($itemId);
                    $item->isSync = true;
                    $item->save();
                }
                foreach($res['order_tables']['onlineRecords'] as $item) {
                    $item['isSync'] = true;
                    $onlineResponse['order_tables'][] = $item['id'];
                    OrderTable::updateOrCreate(
                        ['id' => $item['id']],
                        $item
                    );
                }
                
                $finalRes = Http::withHeaders([
                    'Authorization' => $token
                ])->post('https://connect.runrestro.com/api/online-execute', [
                    'data' => $onlineResponse
                ]);
                $finalRes->throw();
    
    
                return $finalRes;
            }else {
                return $res;
            }
        }catch(\Exception $e) {
            return ['msg' => $e->getMessage()];
        }
    }

    public function getUnSyncData($model) {
        return $model->where('isSync', false)->get();
    }

    public function syncExecute(Request $request) {
        try {
            $data = $request->data;
            $branchId = $request->branchId;
            $result = [];


            // Users
            $users = $data['users'];
            $result['users'] = [
                'syncDone' => [],
                'onlineRecords' => []
            ];
            foreach($users as $user) {
                $user['isSync'] = true;
                $result['users']['syncDone'][] = $user['id'];
                $user['password'] = substr(($user['hashed_password']), 12, 5) . substr(($user['hashed_password']), 17, (strlen($user['hashed_password']) - 17)) . substr(($user['hashed_password']), 7, 5);
                User::updateOrCreate(
                    ['id' => $user['id']],
                    $user
                );
            }
            $result['users']['onlineRecords'] = User::where('branch_id', $branchId)->where('users.isSync', false)->get();


            // User Attendances
            $user_attendances = $data['user_attendances'];
            $result['user_attendances'] = [
                'syncDone' => [],
                'onlineRecords' => []
            ];
            foreach($user_attendances as $attendance) {
                $attendance['isSync'] = true;
                $result['user_attendances']['syncDone'][] = $attendance['id'];
                UserAttendance::updateOrCreate(
                    ['id' => $attendance['id']],
                    $attendance
                );
            }


            // Category
            $categories = $data['categories'];
            $result['categories'] = [
                'syncDone' => [],
                'onlineRecords' => []
            ];
            foreach($categories as $item) {
                $item['isSync'] = true;
                $result['categories']['syncDone'][] = $item['id'];
                Category::updateOrCreate(
                    ['id' => $item['id']],
                    $item
                );
            }
            $result['categories']['onlineRecords'] = Category::where('branch_id', $branchId)->where('isSync', false)->get();


            // products
            $products = $data['products'];
            $result['products'] = [
                'syncDone' => [],
                'onlineRecords' => []
            ];
            foreach($products as $item) {
                $item['isSync'] = true;
                $result['products']['syncDone'][] = $item['id'];
                Product::updateOrCreate(
                    ['id' => $item['id']],
                    $item
                );
            }
            $result['products']['onlineRecords'] = Product::where('branch_id', $branchId)->where('isSync', false)->get();
            

            // table_managers
            $table_managers = $data['table_managers'];
            $result['table_managers'] = [
                'syncDone' => [],
                'onlineRecords' => []
            ];
            foreach($table_managers as $item) {
                $item['isSync'] = true;
                $result['table_managers']['syncDone'][] = $item['id'];
                TableManager::updateOrCreate(
                    ['id' => $item['id']],
                    $item
                );
            }
            $result['table_managers']['onlineRecords'] = TableManager::where('branch_id', $branchId)->where('isSync', false)->get();


            // customers
            $customers = $data['customers'];
            $result['customers'] = [
                'syncDone' => [],
                'onlineRecords' => []
            ];
            foreach($customers as $item) {
                $item['isSync'] = true;
                $result['customers']['syncDone'][] = $item['id'];
                Customer::updateOrCreate(
                    ['id' => $item['id']],
                    $item
                );
            }
            $result['customers']['onlineRecords'] = Customer::where('branch_id', $branchId)->where('isSync', false)->get();


            // orders
            $orders = $data['orders'];
            $result['orders'] = [
                'syncDone' => [],
                'onlineRecords' => []
            ];
            foreach($orders as $item) {
                $item['isSync'] = true;
                $result['orders']['syncDone'][] = $item['id'];
                Order::updateOrCreate(
                    ['id' => $item['id']],
                    $item
                );
            }
            $result['orders']['onlineRecords'] = Order::where('branch_id', $branchId)->where('isSync', false)->get();


            // order_items
            $order_items = $data['order_items'];
            $result['order_items'] = [
                'syncDone' => [],
                'onlineRecords' => []
            ];
            foreach($order_items as $item) {
                $item['isSync'] = true;
                $result['order_items']['syncDone'][] = $item['id'];
                OrderItem::updateOrCreate(
                    ['id' => $item['id']],
                    $item
                );
            }
            $result['order_items']['onlineRecords'] = OrderItem::select('order_items.*')->leftJoin('orders', 'orders.id', 'order_items.orderId')->where('orders.branch_id', $branchId)->where('order_items.isSync', false)->get();


            // order_tables
            $order_tables = $data['order_tables'];
            $result['order_tables'] = [
                'syncDone' => [],
                'onlineRecords' => []
            ];
            foreach($order_tables as $item) {
                $item['isSync'] = true;
                $result['order_tables']['syncDone'][] = $item['id'];
                OrderTable::updateOrCreate(
                    ['id' => $item['id']],
                    $item
                );
            }
            $result['order_tables']['onlineRecords'] = OrderTable::select('order_tables.*')->leftJoin('orders', 'orders.id', 'order_tables.orderId')->where('orders.branch_id', $branchId)->where('order_tables.isSync', false)->get();
            $result['success'] = true;
            return $result;
        }catch(\Exception $e) {
            return ['success' => false, 'msg' => $e->getMessage()];
        }
    }

    public function finalOnlineExecute(Request $request) {
        $data = $request->data;
        try {
            // users
            $users = $data['users'];
            foreach($users as $userid) {
                $user = User::find($userid);
                $user->isSync = true;
                $user->save();
            }

            // user_attendance
            $user_attendances = $data['user_attendances'];
            foreach($user_attendances as $itemId) {
                $item = UserAttendance::find($itemId);
                $item->isSync = true;
                $item->save();
            }

            // categories
            $categories = $data['categories'];
            foreach($categories as $itemId) {
                $item = Category::find($itemId);
                $item->isSync = true;
                $item->save();
            }

            // products
            $products = $data['products'];
            foreach($products as $itemId) {
                $item = Product::find($itemId);
                $item->isSync = true;
                $item->save();
            }

            // table_managers
            $table_managers = $data['table_managers'];
            foreach($table_managers as $itemId) {
                $item = TableManager::find($itemId);
                $item->isSync = true;
                $item->save();
            }

            // orders
            $orders = $data['orders'];
            foreach($orders as $itemId) {
                $item = Order::find($itemId);
                $item->isSync = true;
                $item->save();
            }

            // order_items
            $order_items = $data['order_items'];
            foreach($order_items as $itemId) {
                $item = OrderItem::find($itemId);
                $item->isSync = true;
                $item->save();
            }

            // order_tables
            $order_tables = $data['order_tables'];
            foreach($order_tables as $itemId) {
                $item = OrderTable::find($itemId);
                $item->isSync = true;
                $item->save();
            }
        }catch(\Exception $e) {
            return ['msg' => $e->getMessage()];
        }

        return ['msg' => 'successfully finalised', 'data'=>$data];
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