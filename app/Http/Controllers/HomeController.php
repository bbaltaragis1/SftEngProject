<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\User;
use DB;
use App\Events\NewRequest;

class HomeController extends Controller
{

    protected $redirectTo = '/home';
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //User::all();

        if(is_null(Auth::user()->lat)){
           return redirect()->intended("/profile/edit");
        }
        
        $user_id = Auth::user()->id;
        $lat = Auth::user()->lat;
        $lng = Auth::user()->lng;
        $radius = 50;
        $users = DB::select(DB::raw("SELECT*,
        ( 3959 * acos( cos( radians({$lat}) ) * cos( radians( `lat` ) ) * cos( radians( `lng` ) - radians({$lng}) ) + sin( radians({$lat}) ) * sin( radians( `lat` ) ) ) ) AS distance
        FROM `users` AS u
        where u.lng AND u.lat and u.id not in 
                                        (SELECT id FROM `users` as user WHERE user.id = {$user_id})
        HAVING distance <= {$radius}
        ORDER BY distance ASC"));       
                
        $friends = DB::select(DB::raw(
            "select user1_id, user2_id, accepted from friends where user1_id = {$user_id} or user2_id = {$user_id}"
        ));
        
        return view('home.index' , compact('users', 'radius', 'friends'));
    }
    public function search(Request $request)
    {
        $user_id = Auth::user()->id;
        $radius = (int)$request->input('radius');
        $lat = Auth::user()->lat;
        $lng = Auth::user()->lng;
        $users = DB::select(DB::raw("SELECT*,
        ( 3959 * acos( cos( radians({$lat}) ) * cos( radians( `lat` ) ) * cos( radians( `lng` ) - radians({$lng}) ) + sin( radians({$lat}) ) * sin( radians( `lat` ) ) ) ) AS distance
        FROM `users` AS u
        where u.lng AND u.lat and u.id not in 
                                        (SELECT id FROM `users` as user WHERE user.id = {$user_id})
        HAVING distance <= {$radius}
        ORDER BY distance ASC")); 

        $friends = DB::select(DB::raw(
            "select user1_id, user2_id, accepted from friends where user1_id = {$user_id} or user2_id = {$user_id}"
        ));

        return view('home.index' , compact('users', 'radius', 'friends'));
    }
  
    public function sendFriendReq($id)
    {
        $user_id = Auth::user()->id;
        $wasReqSent = DB::table('friends')->where(
            ['user1_id' => $user_id, 'user2_id' => $id]
        )->get();        

        if ($wasReqSent->count()) {
            //do nothing, req already sent
        } else {
            DB::table('friends')->insert(
                ['user1_id' => $user_id, 'user2_id' => $id]
            );
        }
        broadcast(new NewRequest($user_id, $id));

        return redirect()->intended("/home");
    }

    public function acceptFriendReq($id)
    {
        $user_id = Auth::user()->id;
        DB::table('friends')->where(
            ['user1_id' => $id, 'user2_id' => $user_id]
        )->update(['accepted'=>1]);
        return redirect()->intended("/group");
    }

    public function deleteFriendReq($id)
    {
        $user_id = Auth::user()->id;
        DB::table('friends')->where(
            ['user1_id' => $id, 'user2_id' => $user_id]
        )->delete();
        DB::table('friends')->where(
            ['user2_id' => $id, 'user1_id' => $user_id]
        )->delete();

        return redirect()->back();
    }

    public function fetchReqs()
    {
        $friendRequests = DB::select(DB::raw("SELECT * FROM users WHERE id IN 
            (SELECT user1_id FROM friends
             WHERE accepted = 0 AND user2_id = " . Auth::user()->id . ")"
         ));

         return $friendRequests;
    }

    public function getOwner($group_id)
    {
        $owner = DB::select(DB::raw("SELECT user_id FROM 
                    group_user WHERE group_id = ".$group_id." ORDER BY group_user.id DESC LIMIT 1;"
         ));
        
         return $owner;
    }
    public function getFriends()
    {
        $friends = DB::select(DB::raw("SELECT * FROM users WHERE id IN 
            (SELECT user1_id FROM friends
            WHERE accepted = 1 AND (user1_id = ". Auth::user()->id ." or user2_id = " . Auth::user()->id . "))"
        ));
        
         return $friends;
    }
    public function addFriends(Request $request)
    {
        dd($request);
    }
    public function deleteGroupMembers($group_id)
    {
        
    }
    public function getMembersOfGroup($group_id)
    {
        $groupMem = DB::select(DB::raw("SELECT * FROM users WHERE id IN
        (select user_id from group_user where group_id = {$group_id})"));
        
         return $groupMem;
    }
}
