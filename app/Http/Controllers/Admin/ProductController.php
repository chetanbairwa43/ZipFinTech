<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Tax;
use App\Helper\Helper;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Product::query();

        if($request->keyword){
            $data['keyword'] = $request->keyword;

            $query->where('name', 'like', '%'.$data['keyword'].'%');
        }

        if($request->status){
            $data['status'] = $request->status;

            if($request->status == 'active'){
                $query->where('status', '=', 1);
            }
            else {
                $query->where('status', '=', 0);
            }
        }

        if($request->category){
            $data['category'] = $request->category;

            $query->where('category_id', '=', $data['category']);
        }

        if($request->items){
            $data['items'] = $request->items;
        }
        else{
            $data['items'] = 10;
        }

        $data['data'] = $query->orderBy('created_at','DESC')->with('Category')->paginate($data['items']);
        $data['categories'] = Category::getAllActiveCategoriesNameId();

        return view('admin.product.index',$data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['categories'] = Category::getAllActiveCategoriesNameId();
        $data['units'] = Helper::units();
        $data['tax'] = Tax::getAllActiveTaxes();

        return view('admin.product.create',$data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate(
            [
                'title' => 'required | string | unique:products,name,'.$request->id,
                'category' => 'required',
                'SKU' => 'required | unique:products,SKU,'.$request->id,
                'qty' => 'required | numeric',
                'qty_type' => 'required',
                'min_qty' => 'required',
                'max_qty' => 'required | gte:min_qty',
                'market_price' => 'required | numeric',
                'regular_price' => 'required | numeric | lte:market_price',
                'status' => 'required',
            ] + (!empty($request->id) ? ['image' => 'mimes:jpeg,png,jpg'] : ['image' => 'required | mimes:jpeg,png,jpg'])
        );

        $imagePath = config('app.product_image');

        $pages = Product::updateOrCreate(
            [
                'id' => $request->id,
            ],
            [
                'name' => $request->title,
                'category_id' => $request->category,
                'SKU' => strtoupper($request->SKU),
                'qty' => $request->qty,
                'qty_type' => $request->qty_type,
                'min_qty' => $request->min_qty,
                'max_qty' => $request->max_qty,
                'market_price' => $request->market_price,
                'regular_price' => $request->regular_price,
                'content' => $request->content,
                'tax_id' => $request->tax_percent,
                'tax_id_2' => $request->tax_percent_2,
                'image' => $request->hasfile('image') ? Helper::storeImage($request->file('image'),$imagePath,$request->imageOld) : (isset($request->imageOld) ? $request->imageOld : ''),
                'status' => $request->status,
            ]
        );

        $result = $pages->update();

        if($result)
        {
            return redirect()->route('admin.admin-products.index');
        }
        else
        {
            return redirect()->back()->with('error', 'Something went Wrong, Please try again!');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data['data'] = Product::getProductDetailsByID($id);

        return view('admin.product.show',$data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['categories'] = Category::getAllActiveCategoriesNameId();
        $data['units'] = Helper::units();
        $data['data'] = Product::getProductDetailsByID($id);
        $data['tax'] = Tax::getAllActiveTaxes();

        return view('admin.product.create',$data);
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
        try {
            $data= Product::where('id',$id)->first();
            $result = $data->delete();
            if($result) {
                return response()->json(["success" => true]);
            }
            else {
                return response()->json(["success" => false]);
            }
        }  catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message'  => "Something went wrong, please try again!",
                'error_msg' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Change the specified resource status from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus($id, Request $request)
    {
        try {
            $data= Product::where('id',$id)->first();
            if($data) {
                $data->status = $data->status == 1 ? 0 : 1;
                $data->save();
                return response()->json(["success" => true, "status"=> $data->status]);
            }
            else {
                return response()->json(["success" => false]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message'  => "Something went wrong, please try again!",
                'error_msg' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get Tax of Particular Category
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getTaxByCategory($id, Request $request)
    {
        $category_tax = Category::getTaxIdByCategoryId($id);

        return response()->json(['success' => true, 'output' => $category_tax->tax->id ?? '']);
    }
}
