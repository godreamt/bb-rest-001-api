<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstName', 'lastName', 'profilePic', 'isActive', 'email', 'mobileNumber', 'roles', 'password', 'branch_id'
    ];

    

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::addGlobalScope('role_handler', function (Builder $builder) {
    //         $user = \Auth::user();
    //         if($user->roles != 'Super Admin') {
    //             $builder->where('branch_id',  $user->branch_id);
    //         }
    //     });
    // }

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
    ];

    public function branch()
    {
        return $this->belongsTo('App\Branch');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return ['user_id' => $this->id, 'roles' => $this->roles];
    }
}
