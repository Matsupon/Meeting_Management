<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddUserRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        $user = User::find(session('user_id'));
        if (!$user) {
            return redirect()->route('login');
        }
        
        $users = [];
        if ($user->is_admin) {
            $users = User::orderBy('created_at', 'desc')->get();
        }

        return view('profile', compact('user', 'users'));
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = User::find(session('user_id'));
        if (!$user) {
            abort(404, 'User not found');
        }

        $user->full_name = $request->full_name;
        $user->name = $request->full_name; // Also update name for backward compatibility
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        session(['user_name' => $user->full_name, 'user_email' => $user->email]);

        return back()->with('success', 'Profile updated successfully!');
    }

    public function uploadProfilePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = User::find(session('user_id'));
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        if ($request->hasFile('profile_picture')) {
            // Delete old picture using the stored path directly
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            $file = $request->file('profile_picture');
            $filename = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
            
            // Store full relative path (e.g. 'profile_pictures/123_abc.jpg') for consistency with attachments
            $path = Storage::disk('public')->putFileAs('profile_pictures', $file, $filename);

            $user->profile_picture = $path;
            $user->save();

            return response()->json(['success' => true, 'filename' => $filename, 'path' => $path]);
        }

        return response()->json(['success' => false, 'message' => 'No file uploaded'], 400);
    }

    public function addUser(AddUserRequest $request)
    {
        User::create([
            'name' => $request->full_name,
            'full_name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'User added successfully!');
    }

    public function deleteUser($id)
    {
        $user = User::find(session('user_id'));
        if (!$user || !$user->is_admin) {
            abort(403, 'Unauthorized action.');
        }

        if ($user->id == $id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $targetUser = User::find($id);
        if ($targetUser) {
            // Delete profile picture if exists
            if ($targetUser->profile_picture && Storage::disk('public')->exists($targetUser->profile_picture)) {
                Storage::disk('public')->delete($targetUser->profile_picture);
            }
            $targetUser->delete();
            return back()->with('success', 'User permanently deleted!');
        }

        return back()->with('error', 'User not found.');
    }
} 
