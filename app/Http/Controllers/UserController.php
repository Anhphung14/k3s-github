<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search   = $request->search;
        $paginate = is_numeric($request->paginate) && $request->paginate > 0 ? $request->paginate : 10;

        $filters = $request->only(['search', 'paginate']);

        $usersData = User::when($search, function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%");
        })->paginate($paginate)->appends($filters);

        return Inertia::render('user/Index', [
            'userData' => $usersData,
            'filters'  => $filters,
            'roles'    => Role::all(),
            'qty_user' => User::count(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::get();

        return Inertia::render('user/Form', [
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name'         => 'required',
            'last_name'          => 'required',
            'username'           => 'required|unique:users',
            'phone'              => 'required|numeric|unique:users',
            'role'               => 'required',
            'password'           => 'required',
            'profile_photo_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2000',
        ], [
            'first_name.required'      => 'Please enter a First Name.',
            'last_name.required'       => 'Please enter a Last Name.',
            'username.required'        => 'Please enter a username.',
            'username.unique'          => 'This username is already in use.',
            'phone.required'           => 'Please enter a phone number.',
            'phone.numeric'            => 'Phone number must be numeric.',
            'phone.unique'             => 'This phone number is already in use.',
            'role.required'            => 'Please select a role for the user.',
            'password.required'        => 'Please enter a password.',
            'profile_photo_path.image' => 'Avatar must be an image file.',
            'profile_photo_path.mimes' => 'Only JPG, PNG, and GIF files are allowed.',
            'profile_photo_path.max'   => 'Avatar size must not exceed 2MB.',
        ]);

        $avatarPath = null;
        if ($request->hasFile('profile_photo_path')) {
            $avatarPath = $request->file('profile_photo_path')->store('avatars', 'public');
        }

        $user = User::create([
            'name'               => $request->first_name . ' ' . $request->last_name,
            'first_name'         => $request->first_name,
            'last_name'          => $request->last_name,
            'username'           => $request->username,
            'email'              => $request->email,
            'phone'              => $request->phone,
            'password'           => bcrypt($request->password),
            'profile_photo_path' => $avatarPath,
        ]);

        $role = Role::find($request->role);
        if (! empty($role)) {
            $user->assignRole($role->name);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'user created successfully',
                'data'    => $user,
            ]);
        }

        return redirect()->route('users.index')->with('created', 'user created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        if (! empty($user->profile_photo_path)) {
            $user->profile_photo_path = asset('storage/' . $user->profile_photo_path);
        }
        $user->usermeta = $user->usermeta->toArray();
        $user->role     = $user->roles()->first() ? $user->roles()->first()->name : '';
        return Inertia::render('user/View', [
            'user' => $user,
        ]);
    }

    public function profile()
    {
        $user          = User::findOrFail(Auth::user()->id);
        $user->address = $user->getMetaValue('address');
        $user->zipcode = $user->getMetaValue('zipcode');
        $user->role    = $user->roles()->first() ? $user->roles()->first()->name : '';
        if (! empty($user->profile_photo_path)) {
            $user->profile_photo_path = asset('storage/' . $user->profile_photo_path);
        }
        return Inertia::render('profile/profile', ['user' => $user]);
    }

    public function profileEdit()
    {
        $user = User::findOrFail(Auth::user()->id);
        if (! empty($user->profile_photo_path)) {
            $user->profile_photo_path = asset('storage/' . $user->profile_photo_path);
        }

        $user->address = $user->getMetaValue('address');
        $user->zipcode = $user->getMetaValue('zipcode');

        return Inertia::render('profile/ProfileEdit', [
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        if (! empty($user->profile_photo_path)) {
            $user->profile_photo_path = asset('storage/' . $user->profile_photo_path);
        }
        return Inertia::render('user/Edit', [
            'user'         => $user,
            'roles'        => Role::all(),
            'role_current' => $user->roles()->first(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'first_name' => 'sometimes|required',
            'last_name'  => 'sometimes|required',
            'username'   => 'sometimes|required|unique:users,username,' . $user->id . ',id',
            'phone'      => 'sometimes|required|numeric|unique:users,phone,' . $user->id . ',id',
            'role'       => 'sometimes|required',
            'password'   => 'sometimes',
        ]);

        $data = $request->except('role', 'password');

        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }

        if ($request->hasFile('profile_photo_path')) {

            if ($user->profile_photo_path) {
                Storage::delete($user->profile_photo_path);
            }

            $filePath                   = $request->file('profile_photo_path')->store('avatars', 'public');
            $data['profile_photo_path'] = $filePath;
        }

        $user->update($data);

        $metaKeys = ['address', 'zipcode'];
        foreach ($metaKeys as $key) {
            if (! empty($request->input($key))) {
                UserMeta::updateOrCreate(
                    ['user_id' => $user->id, 'meta_key' => $key],
                    ['meta_value' => $request->input($key)]
                );
            }
        }

        if (! empty($request->role)) {
            $user->roles()->detach();

            $role = Role::find($request->role);
            if ($role) {
                $user->assignRole($role->name);
            } else {
                return redirect()->back()->with('error', 'Invalid role selected.');
            }
        }

        return redirect()->back()->with('created', 'user updated successfully');
    }

    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password'     => ['required', 'string', Password::defaults(), 'confirmed'],
        ]);

        $user = User::findOrFail($id);

        if (! Hash::check($request->input('current_password'), $user->password)) {
            return redirect()->back()->with([
                'error' => 'Incorrect current password.',
            ]);
        }

        $user->password = Hash::make($request->input('new_password'));
        $user->save();

        return redirect()->back()->with('success', 'Password updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        if (empty($user)) {
            return redirect()->back()->with('error', 'user deleted faild!');
        }
        $user->delete();
        return redirect()->back()->with('updated', 'user deleted successfully',);
    }
}
