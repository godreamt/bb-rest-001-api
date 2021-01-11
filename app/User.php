<?php

namespace App;

use App\User;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'firstName', 
        'lastName', 
        'profilePic', 
        'isActive', 
        'email', 
        'mobileNumber', 
        'roles', 
        'password',
        'company_id', 
        'branch_id',
        'attendaceRequired',
        'isSync',
        'password'
    ];

    
    protected $appends = [
        'hashed_password'
    ];


    public function getHashedPasswordAttribute() {
        return "FORSYNC".substr(($this->password), -5) . substr(($this->password), 0, 5) . substr(($this->password), 5, (strlen($this->password) - 10));
    }
    

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('role_handler', function (Builder $builder) {
            // $user = \Auth::user();
            // if($user instanceof User) {
            //     if($user->roles != 'Super Admin') {
            //         $builder->where('company_id',  $user->company_id);
            //     }
            //     if($user->roles != 'Super Admin' && $user->roles != 'Company Admin' && $user->roles != 'Company Accountant') {
            //         $builder->where('branch_id',  $user->branch_id);
            //     }
            // }
        });

        static::updating(function ($user) {
            $restrictedRoles = [ 
                'Company Accountant', 
                'Branch Accountant', 
                'Branch Manager', 
                'Branch Order Manager', 
                'Kitchen Manager', 
                'Bearer'
            ];

            $loggedUser = \Auth::user();
            if($loggedUser instanceof User) {
                if(in_array($loggedUser->roles, $restrictedRoles) && $loggedUser->id != $user->id) {
                    throw new ValidationException('Access denied');
                }
                if($loggedUser->roles != 'Super Admin') {
                    $user->company_id = $loggedUser->company_id;
                }
                if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin' && $loggedUser->roles != 'Company Accountant') {
                    $user->branch_id = $loggedUser->branch_id;
                }
            }
        });

        static::creating(function ($user) {
            $restrictedRoles = [ 
                'Company Accountant', 
                'Branch Accountant', 
                'Branch Manager', 
                'Branch Order Manager', 
                'Kitchen Manager', 
                'Bearer'
            ];
            // throw new ValidationException('test the code first');
            $loggedUser = \Auth::user();
            if($loggedUser instanceof User) {
                if(in_array($loggedUser->roles, $restrictedRoles) && $loggedUser->id != $user->id) {
                    throw new ValidationException('Access denied');
                }
                if($loggedUser->roles != 'Super Admin') {
                    $user->company_id = $loggedUser->company_id;
                }
                if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin' && $loggedUser->roles != 'Company Accountant') {
                    $user->branch_id = $loggedUser->branch_id;
                }
            }
            // \Debugger::dump(Config::get('app.hosted'));
            if(empty($user->id)) {
                $prefix = Config::get('app.hosted') . substr(($loggedUser->company_id ?? ""), -3) . substr(($loggedUser->branch_id ?? ""), -3);
                $user->id = IdGenerator::generate(['table' => 'users', 'length' => 20, 'prefix' => $prefix, 'reset_on_prefix_change' => true]);
            }
        });
        
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        // 'company_id' => 'int',
        // 'branch_id' => 'int',
        'isActive' => 'boolean',
        'isSync' => 'boolean',
        'attendaceRequired' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo('App\Branch', 'branch_id');
    }

    public function attendances()
    {
        return $this->hasMany('App\UserAttendance', 'user_id');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return ['user_id' => $this->id, 'roles' => $this->roles, 'branch_id'=>$this->branch_id, 'company_id'=>$this->company_id];
    }
}
