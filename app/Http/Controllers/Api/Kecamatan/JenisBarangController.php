<?php

namespace App\Http\Controllers\Api\Kecamatan;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Models\Barang\JenisBarang;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class JenisBarangController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('role:kecamatan');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Mengambil data jenis barang dengan pagination (10 data per halaman)
            $jenis_barang = JenisBarang::whereNull('deleted_by')->whereNull('deleted_at')->paginate(10);

            // Mengembalikan response sukses dengan data jenis barang
            return ApiResponse::success($jenis_barang);

            // Kode ini tidak akan pernah dijalankan karena return sebelumnya sudah dipanggil
            return ApiResponse::badRequest();
        } catch (\Throwable $th) {
            // Menangkap exception dan mengembalikan pesan error dengan status 500 (internal server error)
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Memulai transaksi database
            DB::beginTransaction();

            // Validasi input dari request
            $validator = Validator::make($request->all(), [
                'jenis_barang' => 'required|string|max:15', // jenis_barang wajib, maksimal 15 karakter
            ]);

            // Jika validasi gagal, kembalikan error validasi dengan status 422
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            // Membuat data jenis barang baru dengan kunci untuk mencegah konflik
            $store_jenis_barang = JenisBarang::lockForUpdate()->create([
                'JenisBarang' => $request->jenis_barang,
                'LastUpdateDate' => now(),
            ]);

            // Jika berhasil, commit transaksi dan kembalikan response sukses
            if ($store_jenis_barang) {
                DB::commit();
                return ApiResponse::created($store_jenis_barang);
            }

            // Rollback jika gagal
            DB::rollback();
            return ApiResponse::badRequest();
        } catch (\Throwable $th) {
            // Rollback transaksi jika terjadi exception
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            // Mencari data jenis barang berdasarkan IDJenisBarang
            $jenis_barang = JenisBarang::where('IDJenisBarang', $id)->first();

            // Jika ditemukan, kembalikan response sukses
            if (!is_null($jenis_barang)) {
                return ApiResponse::success($jenis_barang);
            }

            // Jika tidak ditemukan, kembalikan response bad request
            return ApiResponse::badRequest();
        } catch (\Throwable $th) {
            // Menangkap exception dan mengembalikan pesan error dengan status 500
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            // Memulai transaksi database
            DB::beginTransaction();

            // Validasi input dari request
            $validator = Validator::make($request->all(), [
                'jenis_barang' => 'required|string|max:15', // jenis_barang wajib, maksimal 15 karakter
            ]);

            // Jika validasi gagal, kembalikan error validasi dengan status 422
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            // Update data jenis barang berdasarkan IDJenisBarang
            $update_jenis_barang = JenisBarang::where('IDJenisBarang', $id)->update([
                'JenisBarang' => $request->jenis_barang,
                'LastUpdateDate' => now(),
            ]);

            // Jika update berhasil, commit transaksi dan kembalikan response sukses
            if ($update_jenis_barang == 1) {
                DB::commit();
                return ApiResponse::success(JenisBarang::where('IDJenisBarang', $id)->first());
            }

            // Rollback jika update gagal
            DB::rollback();
            return ApiResponse::badRequest();
        } catch (\Throwable $th) {
            // Rollback transaksi jika terjadi exception
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function delete($id)
    {
        try {
            DB::beginTransaction();

            $jenis_barang = JenisBarang::where('IDJenisBarang', $id)->update([
                'deleted_at' => Carbon::now(),
                'deleted_by' => Auth::user()->id,
            ]);
            if ($jenis_barang) {
                DB::commit();
                return ApiResponse::success('jenis barang berhasil dihapus');
            } else {
                DB::rollBack();
                return ApiResponse::badRequest('jenis barang gagal dihapus');
            }
        } catch (Exception $e) {
            return ApiResponse::badRequest($e->getMessage());
        }
    }

}
