<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'Transaksi',
            'content' => 'transaksi',
            'item' => Item::with('kategori')->get()
        ];

        return view('layout.index',['data' => $data]);
    }

    public function addTransaksi(Request $request)
    {
        $trans = new Transaksi;
        $trans->nomor_transaksi = $this->generateBarcodeNumber();
        $trans->tgl_transaksi = date('Y-m-d H:i:s');
        $trans->id_user = session('id_user');
        $trans->total = $request->input('total');
        if($trans->save()){
            if($request->has('id_item')){
                foreach ($request->input('id_item') as $i => $td) {
                    $transDetail = new TransaksiDetail;
                    $item = new Item();
                    $transDetail->id_item = $td;
                    $transDetail->id_transaksi = $trans->id_transaksi;
                    $transDetail->qty = $request->input('qty')[$i];
                    $transDetail->subtotal = $request->input('subtotal')[$i];
                    $transDetail->save();
                    $dataItem = DB::table('items')->where('id_item', $td)->get();
                    $qty = $dataItem[0]->stok_item - $request->input('qty')[$i];
                    $item->where('id_item', $td)
                        ->update(['stok_item' => $qty]);
                }
                $checkTrans = TransaksiDetail::where('id_transaksi',$trans->id_transaksi)->first();
                if($checkTrans){
                    return redirect()->back()->with('success','Berhasil menambah transaksi!');
                }else{
                    return redirect()->back()->with('error','Gagal menambah transaksi!');
                }
            }else{
                return redirect()->back()->with('error','Detail transaksi tidak boleh kosong!');
            }
        }else{
            return redirect()->back()->with('error','Terjadi kesalahan menyimpan transaksi! coba lagi.');
        }
    }

    public function generateBarcodeNumber() {
        $number = mt_rand(1000000000, 9999999999); // better than rand()

        // call the same function if the barcode exists already
        // if (barcodeNumberExists($number)) {
        //     return generateBarcodeNumber();
        // }

        // otherwise, it's valid and can be used
        return $number;
    }

    public function loadData(Request $request)
    {
        $whereLike = [
            'nomor_transaksi',
            'tgl_transaksi',
            'username'
        ];

        $start  = $request->input('start');
        $length = $request->input('length');
        $order  = $whereLike[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $totalData = Transaksi::with('user')->count();
        if (empty($search)) {
            $queryData = Transaksi::with('user')
                ->offset($start)
                ->limit($length)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = Transaksi::with('user')->count();
        } else {
            $queryData =Transaksi::with('user')
                ->where(function($query) use ($search) {
                    $query->where('nomor_transaksi', 'like', "%{$search}%");
                    $query->orWhere('tgl_transaksi','like',"%{$search}%");
                    $query->orWhere('username','like',"%{$search}%");
                })
                ->offset($start)
                ->limit($length)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = Transaksi::with('user')
                ->offset($start)
                ->where(function($query) use ($search) {
                    $query->where('nomor_transaksi', 'like', "%{$search}%");
                    $query->orWhere('tgl_transaksi','like',"%{$search}%");
                    $query->orWhere('username','like',"%{$search}%");
                })
                ->count();
        }

        $response['data'] = [];
        if($queryData <> FALSE) {
            $nomor = $start + 1;
            foreach ($queryData as $val) {
                    $response['data'][] = [
                        $nomor,
                        $val->nomor_transaksi,
                        date('d/m/Y H:i',strtotime($val->tgl_transaksi)),
                        $val->user->username,
                        number_format($val->total),
                        '
                        <a href="javascript:void(0)" class="btn btn-warning"><i class="fas fa-file"></i></a>
                        <a href="javascript:void(0)" class="btn btn-primary" onclick="editTransaksi('.$val->id_transaksi.')"><i class="fas fa-edit"></i></a>
                        <a href="javascript:void(0)" class="btn btn-danger" onclick="deleteTransaksi('.$val->id_transaksi.')"><i class="fas fa-trash"></i></a>
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

    public function list()
    {
        $data = [
            'title' => 'Data Transaksi',
            'content' => 'transaksi_list'
        ];
        return view('layout.index',['data' => $data]);
    }

    public function nota($id)
    {
        $q = Transaksi::with('user')->find($id);
        $data = [
            'title' => 'Nota Transaksi',
            'content' => 'transaksi_nota',
            'data' => $q
        ];
        return view('layout.index',['data' => $data]);
    }

    public function destroy($id)
    {
        $q = Transaksi::find($id);
        if(!$q)
            return response(['status' => 500, 'message' => 'Transaksi tidak ditemukan!']);

        if($q->delete()){
            return response([
                'status' => 200,
                'message' => 'Berhasil menghapus Transaksi!'
            ]);
        }else{
            return response([
                'status' => 500,
                'message' => 'Gagal menghapus Transaksi!'
            ]);
        }
    }

}
