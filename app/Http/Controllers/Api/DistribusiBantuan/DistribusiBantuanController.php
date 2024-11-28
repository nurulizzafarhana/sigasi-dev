<?php

namespace App\Http\Controllers\Api\DistribusiBantuan;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Models\Bantuan\Bantuan;
use App\Models\DistribusiBantuan\DistribusiBantuan;
use App\Models\Posko\Posko;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DistribusiBantuanController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('role:bansos');
        $this->middleware('role:posko|posko-utama')->only('index');
    }

    public function index(Request $request)
    {
        // menampilkan data distribusi bantuan dengan dibatasi 10 record
        $distribusi_bantuan = DistribusiBantuan::whereNull('deleted_by')->whereNull('deleted_at')->with(['posko.user', 'bantuan.bantuanDetail.barang.jenisBarang']);

        // pencarian berdasarkan id posko
        if (isset($request->posko)) {
            $distribusi_bantuan->where('IDPosko', $request->posko);
        }

        // pencarian berdasarkan id bantuan
        if (isset($request->bantuan)) {
            $distribusi_bantuan->where('IDBantuan', $request->posko);
        }

        $distribusi_bantuan = $distribusi_bantuan->paginate(10);
        return ApiResponse::success($distribusi_bantuan);
    }

    public function createOrEdit()
    {
        try {

            $bantuan = Bantuan::whereNull('deleted_by')->whereNull('deleted_at')->get();
            $posko = Posko::whereNull('deleted_by')->whereNull('deleted_at')->get();

            return ApiResponse::success([
                'bantuan' => $bantuan,
                'posko' => $posko
            ]);
        } catch (\Throwable $th) {

            return ApiResponse::badRequest($th->getMessage());
        }
    }

    public function show($id)  // id yang digunakan idposko
    {
        // tampilan data berdasarkan id posko
        $distribusi_bantuan = DistribusiBantuan::with(['posko.user', 'bantuan.bantuanDetail.barang.jenisBarang'])->where('IDdistribusi_bantuan', $id)->first();

        if (is_null($distribusi_bantuan)) {
            return ApiResponse::notFound('Data distribusi bantuan tidak ditemukan.');
        }

        return ApiResponse::success($distribusi_bantuan);
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [ // cek validasi sesuai parameter
                'idPosko' => 'numeric',
                'idBantuan' => 'numeric',
                'tanggalDistribusi' => 'required',
            ]);

            if ($validator->fails()) { // jika parameter ada yang tidak sesuai maka return error
                return ApiResponse::badRequest($validator->errors());
            }

            DB::beginTransaction();

            // Membuat data distribusi bantuan baru
            $distribusi_bantuan = DistribusiBantuan::lockForUpdate()->create([
                'IDPosko' => $request->idPosko,
                'IDBantuan' => $request->idBantuan,
                'TanggalDistribusi' => $request->tanggalDistribusi,
                'Deskripsi' => $request->deskripsi,
            ]);

            // Jika berhasil, komit transaksi dan kembalikan respons sukses
            if ($distribusi_bantuan) {
                DB::commit();
                return ApiResponse::created($distribusi_bantuan);
            } else {
                // Jika gagal, rollback transaksi
                DB::rollback();
                return ApiResponse::badRequest();
            }
        } catch (Exception $e) {
            return ApiResponse::badRequest($e);
        }
    }

    public function update(Request $request, $id) // untuk mengisi jumlah yang diterima
    {
        try {
            $validator = Validator::make($request->all(), [ // cek validasi sesuai parameter
                'idPosko' => 'numeric',
                'idBantuan' => 'numeric',
                'tanggalDistribusi' => 'required',
            ]);

            if ($validator->fails()) { // jika parameter ada yang tidak sesuai maka return error
                return ApiResponse::badRequest($validator->errors());
            }

            DB::beginTransaction(); // memulai transaksi

            // update data distribusi bantuan
            $distribusi_bantuan = DistribusiBantuan::where('IDDistribusiBantuan', $id)->lockForUpdate()->update([
                'IDPosko' => $request->idPosko,
                'IDBantuan' => $request->idBantuan,
                'TanggalDistribusi' => $request->tanggalDistribusi,
                'Deskripsi' => $request->deskripsi,
            ]);

            if ($distribusi_bantuan) {
                DB::commit(); // jika berhasil maka commit data
                $data_distribusi_bantuan = DistribusiBantuan::with(['posko.user', 'bantuan.bantuanDetail.barang.jenisBarang'])->where('IDDistribusiBantuan', $id)->first();
                return ApiResponse::created($data_distribusi_bantuan);
            } else {
                DB::rollBack();
                return ApiResponse::badRequest('Data distribusi bantuan tidak dapat disimpan.');
            }
        } catch (Exception $e) {
            return ApiResponse::badRequest($e);
        }
    }

    public function delete($id)
    {
        try {
            DB::beginTransaction();

            $distribusi_bantuan = DistribusiBantuan::where('IDDistribusiBantuan', $id)->update([
                'deleted_at' => Carbon::now(),
                'deleted_by' => Auth::user()->id,
            ]);

            if ($distribusi_bantuan) {
                DB::commit();
                return ApiResponse::success('Distribusi bantuan berhasil dihapus');
            } else {
                DB::rollBack();
                return ApiResponse::badRequest('Distribusi bantuan gagal dihapus');
            }
        } catch (Exception $e) {
            return ApiResponse::badRequest($e->getMessage());
        }
    }
}
