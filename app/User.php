<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    //For JWTAuth
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    public function setPasswordAttribute($password)
    {
        if ( !empty($password) ) {
            $this->attributes['password'] = bcrypt($password);
        }
    }   



    //Get the companies, the user is admin of
    public function companies(){
        return $this->belongsToMany(Company::class);
    }

    //Gets the climatemasters the user has
    public function climatemasters(){
        return $this->hasMany(Climatemaster::class);
    }

    //Returns all models of Climatemaster_steps_completed (one per year)
    public function climatemaster_steps_completed(){
        return $this->hasMany(Climatemaster_steps_completed::class);
    }

    public function pictures_for_imagecreator(){
        return $this->hasMany(Picture_for_imagecreator::class);
    }

    //Returns the public user profile of this user
    public function public_user_profile(){
        return $this->hasOne(PublicUserProfile::class);
    }

    //Return all the climadviceUserChecks this user has
    public function climadvice_user_checks(){
        return $this->hasMany(ClimadviceUserCheck::class);
    }




    public function logins(){
        return $this->hasMany(Login::class);
    }

    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname', 'lastname', 'username', 'email', 'password','role', 'street', 'house_number', 'postcode', 'residence'
    ];

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

}
