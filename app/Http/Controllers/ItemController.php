<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Kategori;
use Validator;

class ItemController extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'Item List',
            'content' => 'item',
            'kategori' => Kategori::all()
        ];
        return view('layout.index',['data'=>$data]);
    }

    public function loadData(Request $request)
    {
        $whereLike = [
            'nama_item',
            'nama_kategori'
        ];

        $start  = $request->input('start');
        $length = $request->input('length');
        $order  = $whereLike[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $totalData = Item::with('kategori')->count();
        if (empty($search)) {
            $queryData = Item::with('kategori')
                ->offset($start)
                ->limit($length)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = Item::with('kategori')->count();
        } else {
            $queryData = Item::with('kategori')
                ->where(function($query) use ($search) {
                    $query->where('eberkas_login.nama', 'like', "%{$search}%");
                    $query->orWhere('keterangan_log','like',"%{$search}%");
                })
                ->offset($start)
                ->limit($length)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = Item::with('kategori')
                ->offset($start)
                ->where(function($query) use ($search) {
                    $query->where('nama_item', 'like', "%{$search}%");
                    $query->orWhere('nama_kategori','like',"%{$search}%");
                })
                ->count();
        }

        $response['data'] = [];
        if($queryData <> FALSE) {
            $nomor = $start + 1;
            foreach ($queryData as $val) {
                    $response['data'][] = [
                        $nomor,
                        $val->nama_item,
                        $val->kategori->nama_kategori,
                        $val->harga_item,
                        $val->stok_item,
                        $val->expired_item,
                        '
                        <a href="javascript:void(0)" class="btn btn-primary" onclick="editItem('.$val->id_item.')"><i class="fas fa-edit"></i></a>
                        <a href="javascript:void(0)" class="btn btn-danger" onclick="deleteItem('.$val->id_item.')"><i class="fas fa-trash"></i></a>
                        '
                    ];
                $nomor++;
            }
        }

        $response['recordsTotal'] = 0;
        if ($totalData <> FALSE) {
            $response['recordsTotal'] = $totalData;
        }

        $response['recordsFiltered'] = 0;
        if ($totalFiltered <> FALSE) {
            $response['recordsFiltered'] = $totalFiltered;
        }

        return response()->json($response);
    }

    public function createItem()
    {
        $data = [
            'title' => 'Insert New Item',
            'content' => 'item_insert'
        ];
        return view('layout.index',['data' => $data]);
    }

    public function insertItem(Request $request)
    {
        $rules = [
            'nama_item' => 'required',
            'stok_item' => 'required',
            'harga_item' => 'required',
            'id_kategori' => 'required',
            'expired_item' => 'required'
        ];

        $isValid = Validator::make($request->all(),$rules);

        if($isValid->fails()){
            return response([
                'status' =>  400,
                'errors' => $isValid->errors()
            ]);
        }else{

            $item = new Item;
            $item->nama_item = $request->input('nama_item');
            $item->stok_item = $request->input('stok_item');
            $item->harga_item = $request->input('harga_item');
            $item->id_kategori = $request->input('id_kategori');
            $item->expired_item = $request->input('expired_item');

            if($item->save()){
                return response([
                    'status' => 200,
                    'message' => 'Item created successfully!'
                ]);
            }else{
                return response([
                    'status' => 500,
                    'message' => 'Failed to insert a new Item, try again!'
                ]);
            }
        }
    }

    public function editItem($id)
    {
        $data = Item::find($id);
        return response($data);
    }

    public function updateItem(Request $request, $id)
    {
        $rules = [
            'nama_item' => 'required',
            'stok_item' => 'required',
            'harga_item' => 'required',
            'id_kategori' => 'required',
            'expired_item' => 'required'
        ];

        $isValid = Validator::make($request->all(),$rules);

        if($isValid->fails()){
            return response([
                'status' => 400,
                'errors' => $isValid->errors()
            ]);
        }else{

            $item = Item::find($id);
            if($item){
                $item->nama_item = $request->input('nama_item');
                $item->id_kategori = $request->input('id_kategori');
                $item->harga_item = $request->input('harga_item');
                $item->expired_item = $request->input('expired_item');

                if($item->save()){
                    return response([
                        'status' => 200,
                        'message' => 'Item updated successfully!'
                    ]);
                }else{
                    return response([
                        'status' => 500,
                        'message' => 'Failed to update Item!'
                    ]);
                }
            }else{
                return response([
                    'status' => 500,
                    'message' => 'Item not found!'
                ]);
            }
        }
    }

    public function deleteItem($id)
    {
        $item = Item::find($id);
        if(!$item)
            return response(['status' => 401,'message' => 'Item not found']);

        if($item->delete()){
            return response(['status' => 200, 'message' => 'Item deleted successfully']);
        }else{
            return response(['status' => 500, 'message' => 'Failed to delete item, try again!']);
        }
    }

    public function getItem($id)
    {
        $data = Item::with('kategori')->find($id);
        return response($data);
    }
}
