<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * User controller
 */
class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return UserResource::collection(User::orderBy('created_at', 'desc')->paginate(2));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $form_fields = $request->validate([
            'name'=>['required','min:6'],
            'email'=>['required','email',Rule::unique('users','email')],
            'password'=>'required|confirmed|min:6'
        ]);
        $form_fields['password'] = bcrypt($form_fields['password']);
        return User::create($form_fields);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return UserResource::collection(User::where('id',$id)->paginate(1));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::find($id);
        $form_fields = $request->validate([
            'name'=>['required','min:6'],
            'email'=>['required','email'],
            'password'=>'required|confirmed|min:6'
        ]);
        $form_fields['password'] = bcrypt($form_fields['password']);
        $user->update($form_fields);
        return $user;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return User::destroy($id);
    }

    /**
     * Searching for the specified resource in the storage.
     * @param string $name
     */
    public function search(string $name)
    {
        return UserResource::collection(User::where('name','like','%'.$name.'%')->paginate(2));
    }

}
