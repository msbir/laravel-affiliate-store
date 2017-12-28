<?php

namespace App\Http\Controllers\Admin;

use App\Category;
use App\Base\Controllers\AdminController;
use App\Categoryfile;
use App\Http\Requests\Admin\CategoryRequest;
use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Yajra\DataTables\DataTables;

class CategoriesController extends AdminController
{
      /**
       * Create a new controller instance.
       *
       */
      public function __construct()
      {
        $this->middleware('auth');
      }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $categories = Category::all();

      return view('admin.categories.index',compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.categories.create');
    }

  /**
   * Store a newly created resource in storage.
   *
   * @param \App\Http\Controllers\Admin\CategoryRequest|CategoryRequest $request
   * @return \Illuminate\Http\Response
   */
    public function store(CategoryRequest $request)
    {
      $data = [
        'name' => $request->name,
      ];

      $category = Category::create($data);

      if($request->file('image')){
        $files['image'] = time().'_'.$request->file('image')->getClientOriginalName();
        $request->image->move(public_path('/uploads/categories/'.$category->id.'/'), $files['image']);

          if($request->file('second_image')){
            $files['second_image'] = time().'_'.$request->file('second_image')->getClientOriginalName();
            $request->second_image->move(public_path('/uploads/categories/'.$category->id.'/'), $files['second_image']);
          }
          $files['category_id'] = $category->id;
        Categoryfile::create($files);
      }

      return Redirect::route('admin.categories.index');
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
   * @param  int $id
   * @param Request $request
   * @return \Illuminate\Http\Response
   */
    public function edit($id, Request $request)
    {
        $category = Category::findOrFail($id);
        $products = $category->products()->get();

      if ($request->ajax()){
        return Datatables::of($products)
          ->editColumn('link', function ($product){
            return '<a href="'.$product->link.'">Product Link</a>';
          })
          ->addColumn('action', function ($product){
            return '<a class="btn btn-sm btn-warning" href="'.route('admin.products.edit',$product->id).'">Edit</a>';
          })
          ->rawColumns(['link','action'])
          ->make(true);
      }
        return view('admin.categories.edit', compact('category','products'));
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
      $validatedData = $request->validate([
        'name' => 'required|max:255',
        'image' => 'mimes:png,jpg,jpeg',
        'second_image' => 'mimes:png,jpg,jpeg',
      ]);

      $data = [
        'name' => $request->name,
      ];

      if($request->file('image')){
        $data['image'] = time().'-'.$request->file('image')->getClientOriginalName();
        $request->image->move(public_path('/uploads/categories/'.$id), $data['image']);
      }
      if($request->file('second_image')){
        $data['second_image'] = time().'-'.$request->file('second_image')->getClientOriginalName();
        $request->second_image->move(public_path('/uploads/categories/'.$id), $data['second_image']);
      }

      $category = Category::findOrFail($id);
      $category->update($data);

      return Redirect::route('admin.categories.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        $category->delete();

        return Redirect::route('admin.categories.index');
    }
}
