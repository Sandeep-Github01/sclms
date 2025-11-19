<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserApiController extends Controller
{
    protected $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return response()->json(User::all());
    }

    public function store(Request $request)
    {
        return response()->json($this->service->create($request->all()));
    }

    public function update(Request $request, User $user)
    {
        return response()->json($this->service->update($user, $request->all()));
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
