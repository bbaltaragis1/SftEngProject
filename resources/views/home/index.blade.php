@extends ('layouts.app')

@section ('content')
<form method="post" action="{{ action('HomeController@search') }}">
                  {{ csrf_field() }}
                <div>
                  <p>Radius for PlayDates</p>  
                  <input type="range" min="1" max="100" value="{{$radius}}" id="radius" name="radius">
                  <p>Radius: <span id="value"></span></p>
                </div>
                <div>
                  <button type="submit" class="btn btn-primary">Search</button>
                </div>
              </form>
<table class="table table-striped table-sm">
              <thead>
                <tr>
                  <th></th>
                  <th>Name</th>
                  <th>Parent Age</th>
                  <th>Child Age</th>
                  <th>City</th>
                  <th></th>
                </tr>
                </thead>
                
                

              @foreach($users as $user)
                  <tr>
                    <td>
                    
                      @if($user->user_picture)
                        <img src= "data:{{$user->user_picture_type}};base64,{{$user->user_picture}}" height="100" width="100">
                      @else
                          <img src="{{ URL::to('/') }}/images/blankProfile.png" height="100" width="100">
                      @endif
                    </td>
                    <td>{{$user->name}}</td>
                    <td>{{$user->parent_age}}</td>
                    <td>{{$user->child_age}}</td>
                    <td>{{$user->city}}</td>
                    <td>
                      <a href="/profile/{{$user->id}}"><button type="button" class="btn btn-success">View Profile</button></a>
                      @foreach($friends as $friend)
                        @if($user->id == $friend->user2_id)
                          @if($friend->user2_id == $user->id and $friend->accepted == 0)
                            <a href="#"><button type="button" class="btn btn-success">Pending</button></a>
                          @elseif($friend->user2_id == $user->id and $friend->accepted == 1)
                            <a href="#"><button type="button" class="btn btn-success">You are friends</button></a>
                          @else
                            <a href="friendrequest/{{$user->id}}"><button type="button" class="btn btn-success">Add friend</button></a>
                          @endif
                        @else
                          <a href="friendrequest/{{$user->id}}"><button type="button" class="btn btn-success">Add friend</button></a>
                          @continue
                        @endif
                      @endforeach
                    </td>
                  </tr>
              @endforeach

              </tbody>
            </table>

@endsection