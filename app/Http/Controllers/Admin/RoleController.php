<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $query = Role::query();

        // if($request->keyword){
        //     $data['keyword'] = $request->keyword;

        //     $query->where('name', 'like', '%'.$data['keyword'].'%');
            
        // }

        // if($request->items){
        //     $data['items'] = $request->items;
        // }
        // else{
        //     $data['items'] = 10;
        // }

        // $data['data'] = $query->orderBy('created_at','DESC')->paginate($data['items']);

        // return view('admin.role.index',$data);

        $data['data'] = Role::all();

        return view('admin.role.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['permissions'] = Permission::all()->pluck('title', 'id');

        return view('admin.role.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required | string | unique:roles,name,'.$request->id,
        ]);

        $role = Role::updateOrCreate(
            [
                'id' => $request->id,
            ],
            [
                'name' => $request->title,
            ]
        );

        $role->permissions()->sync($request->input('permissions', []));

        return redirect()->route('admin.roles.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Role $role)
    {
        $data['permissions'] = Permission::all()->pluck('title', 'id');
        $data['data'] = $role->load('permissions');
        return view('admin.role.create', $data);

        // $permissions = Permission::all()->pluck('title', 'id');

        // $role->load('permissions');

        // return view('admin.role.create', compact('permissions', 'role'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
