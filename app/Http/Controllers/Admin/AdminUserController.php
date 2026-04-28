<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    protected function blockAdminUserCrud(): void
    {
        abort(403, 'Users can only sign up from the web portal.');
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $this->blockAdminUserCrud();
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $this->blockAdminUserCrud();
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $this->blockAdminUserCrud();
    }

    /**
     * Display the specified user.
     */
    public function show($id)
    {
        $this->blockAdminUserCrud();
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit($id)
    {
        $this->blockAdminUserCrud();
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, $id)
    {
        $this->blockAdminUserCrud();
    }

    /**
     * Remove the specified user.
     */
    public function destroy($id)
    {
        $this->blockAdminUserCrud();
    }

    /**
     * Restore a soft-deleted user.
     */
    public function restore($id)
    {
        $this->blockAdminUserCrud();
    }
}
