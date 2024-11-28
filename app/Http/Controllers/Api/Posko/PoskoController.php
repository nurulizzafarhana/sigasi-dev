<?php

namespace App\Http\Controllers\Api\Posko;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Models\Posko\Posko;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PoskoController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        /**
         * Super Posko Utama Access
         */
        $this->middleware('role:posko-utama', ['except' => ['index', 'show']]);

        /**
         * Super Posko Utama and Posko Access
         */
        $this->middleware('role:posko-utama|posko', ['except' => ['create', 'store', 'edit', 'update', 'destroy']]);
    }
    public function index()
    {
        $posko = Posko::with(['user'])->whereNull('deleted_by')->whereNull('deleted_at')->paginate(10); // untuk dapatkan semua data posko, dengan dibatasi 10 data
        return ApiResponse::success($posko);
    }

    public function show($id)
    {
        // menampilkan data posko berdasarkan parameter id dengan relasi user
        $posko = Posko::with(['user'])->where('IDPosko', $id)->first();

        if(is_null($posko)){ // jika posko tidak ada, maka masuk kondisi error
            return ApiResponse::badRequest('Data posko tidak ditemukan.');
        }

        return ApiResponse::success($posko); // data dari posko
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [ // validasi data
                'idUser' => 'required|numeric',
                'location' => 'required|max:50',
                'problem' => 'required',
                'solution' => 'required',

            ]);

            if ($validator->fails()) { // jika ada parameter yang tidak sesuai maka muncul pesan error
                return ApiResponse::badRequest($validator->errors());
            }

            DB::beginTransaction(); // memulai data transaksi
            $posko = Posko::lockForUpdate()->create([ // membuat record baru
                'Ketua' => $request->idUser,
                'Lokasi' => $request->location,
                'Masalah' => $request->problem,
                'SolusiMasalah' => $request->solution,

            ]);
            if ($posko) { // jika kondisi ada maka lakukan commit
                DB::commit();
                return ApiResponse::success($posko);
            } else { // jika datanya tidak ada, maka lakukan error
                DB::rollBack();
                return ApiResponse::badRequest('Data posko tidak dapat disimpan.');
            }
        } catch (Exception $e) { // jika query ada yang salah maka tampil disini
            return ApiResponse::badRequest('Data tidak dapat disimpan.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'idUser' => 'required',
                'location' => 'required|max:50',
                'problem' => 'required',
                'solution' => 'required',

            ]);

            if ($validator->fails()) {
                return ApiResponse::badRequest($validator->errors());
            }

            DB::beginTransaction();
            $posko = Posko::where('IDPosko', $id)->lockForUpdate()->update([ // update data berdasarkan id posko
                'Ketua' => $request->idUser,
                'Lokasi' => $request->location,
                'Masalah' => $request->problem,
                'SolusiMasalah' => $request->solution,

            ]);
            if ($posko) { // jika hasilnya true maka lakukan commit
                DB::commit();
                $data_posko =  Posko::with(['user'])->where('IDPosko', $id)->first(); // amnil data kembali yang terbaru
                return ApiResponse::success($data_posko);
            } else {
                DB::rollBack();
                return ApiResponse::badRequest('Data posko tidak dapat disimpan.');
            }
        } catch (Exception $e) {
            return ApiResponse::badRequest($e);
        }
    }

    public function delete($id)
    {
        try {
            DB::beginTransaction();

            $posko = Posko::where('IDPosko', $id)->update([
                'deleted_at' => Carbon::now(),
                'deleted_by' => Auth::user()->id,
            ]);
            if ($posko) {
                DB::commit();
                return ApiResponse::success('posko berhasil dihapus');
            } else {
                DB::rollBack();
                return ApiResponse::badRequest('posko gagal dihapus');
            }
        } catch (Exception $e) {
            return ApiResponse::badRequest($e->getMessage());
        }
    }

}
