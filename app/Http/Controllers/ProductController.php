<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use App\Models\Bid;
use App\Http\Middleware\Role;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class ProductController extends Controller
{

    public function __construct()
    {
        
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $all_products = Product::orderBy('created_at','DESC')->get();

        return view('product.products', compact('all_products'));

    }

    public function products_by_user($id)
    {
    
        $all_products = Product::orderBy('created_at','DESC')
                   ->where('user_id', $id)->get();

        return view('product.products', compact('all_products'));        

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $all_category = Category::all();

        return view('product.create', compact('all_category'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validator($request); 
        $product = new product();
        $this->savedata($request, $product);
        return redirect()->back()->with(\Session::flash('success', 'Data inserted Successfully.'));
    }

    protected function validator($data){

        
        $data->validate([
            'name'                  => 'required',
            'price'                 => 'required',
            'expired_at'            => 'required',
            'short_description'     => 'required',
            'long_description'      => 'required',
            'specification'         => 'nullable',
            'category'              => 'required',
            'image'                 => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:9096',
        ]);
    }


    public function savedata($request , $product){

        if ($request->hasFile('image')) {
            //if (FileTypeValidate($request->image, ['jpeg', 'jpg', 'png']))
            try{
                $file= $request->file('image');
                $filename= date('YmdHi').$file->getClientOriginalName();
                $file-> move(public_path('assets/images/product'), $filename);
                $request->image = $filename;
            }catch (\Exception $exp) {
                $notify[] = ['error', 'Image could not be uploaded.'];
                return 'image upload error';
                }   
    }


        $product->name = $request->name;
        $product->price = $request->price;
        //$new->total_bid = $request->total_bid;
        $product->expired_at = $request->expired_at;
        //$new->rating = $request->rating;
        //$new->total_rating = $request->total_rating;
        //$new->review = $request->review;
        $product->short_description = $request->short_description;
        $product->long_description = $request->long_description;
        $product->specification = $request->specification;
        $product->image_path = $request->image;
        $product->category_id=$request->category;
        $product->user_id = auth::user()->id;
        $product->save();

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
        //$user = User::with()
        $product = Product::where('id',$id)->first();
        if(isset($product)){
            $category = Category::where('id',$product->category_id)->first();
            $bid_count = Bid::where('product_id',$id)->count();
            $max_bid = Bid::where('product_id',$id)->max('amount');
            $bid_info =array ($bid_count,$max_bid);

            return view('product.view', compact('product','category','bid_info'));
        }
      
        return view('product.view', compact('product'));
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        echo $id;
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


    public function bid(Request $request, $pid)
    {
        //
        $request->validate([
            'amount'      => 'required',
        ]);


        Bid::create([
            'product_id' =>  $pid,
            'user_id' => Auth::user()->id,
            'amount' => $request->amount,
        ]);


        return back()->with(\Session::flash('success', 'Bid Placed Successfully.'));
    }
}
