<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = ['user_id'];

    public function users()
    {


    }


}