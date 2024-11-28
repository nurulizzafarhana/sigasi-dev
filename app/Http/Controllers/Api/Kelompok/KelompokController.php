<?php

namespace App\Http\Controllers\Api\Kelompok;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Models\Kelompok\Kelompok;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class KelompokController extends Controller
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
            // Mengambil daftar Kelompok dengan pagination 10 item per halaman
            $kelompok = Kelompok::whereNull('deleted_by')->whereNull('deleted_at')->paginate(10);

            // Mengembalikan response sukses dengan data kelompok
            return ApiResponse::success($kelompok);
        } catch (\Throwable $th) {
            // Menangkap exception dan mengembalikan pesan error
            return ApiResponse::badRequest($th->getMessage());
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
                'nama_kelompok' => 'required|string|max:20', // Nama kelompok wajib, maksimal 20 karakter
                'keterangan' => 'required|string', // Keterangan wajib, tipe string
            ]);

            // Jika validasi gagal, kembalikan error dengan status 422
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            // Membuat data kelompok baru dengan lock untuk mencegah konflik
            $store = Kelompok::lockForUpdate()->create([
                'NamaKelompok' => $request->nama_kelompok,
                'Keterangan' => $request->keterangan,
            ]);

            // Jika penyimpanan berhasil, commit transaksi dan kembalikan response sukses
            if ($store) {
                DB::commit();
                return ApiResponse::created($store);
            }

            // Rollback transaksi jika gagal
            DB::rollback();
            return ApiResponse::badRequest();
        } catch (\Throwable $th) {
            // Rollback transaksi jika terjadi exception
            DB::rollback();
            return ApiResponse::badRequest($th->getMessage());
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            // Mencari data kelompok berdasarkan IDKelompok
            $kelompok = Kelompok::where('IDKelompok', $id)->first();

            // Jika kelompok ditemukan, kembalikan response sukses
            if (!is_null($kelompok)) {
                return ApiResponse::success($kelompok);
            }

            // Jika kelompok tidak ditemukan, kembalikan response not found
            return ApiResponse::notFound();
        } catch (\Throwable $th) {
            // Tangani exception
            return ApiResponse::badRequest($th->getMessage());
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
                'nama_kelompok' => 'required|string|max:20', // Nama kelompok wajib, maksimal 20 karakter
                'keterangan' => 'required|string', // Keterangan wajib
            ]);

            // Jika validasi gagal, kembalikan error dengan status 422
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            // Update data kelompok berdasarkan IDKelompok
            $update = Kelompok::lockForUpdate()->where('IDKelompok', $id)->update([
                'NamaKelompok' => $request->nama_kelompok,
                'Keterangan' => $request->keterangan,
            ]);

            // Jika update berhasil, commit transaksi dan kembalikan response sukses
            if ($update) {
                DB::commit();
                return ApiResponse::success(Kelompok::where('IDKelompok', $id)->first());
            }

            // Rollback jika update gagal
            DB::rollback();
            return ApiResponse::badRequest();
        } catch (\Throwable $th) {
            // Rollback transaksi jika terjadi exception
            return ApiResponse::badRequest($th->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function delete($id)
    {
        try {
            DB::beginTransaction();

            $kelompok = Kelompok::where('IDKelompok', $id)->update([
                'deleted_at' => Carbon::now(),
                'deleted_by' => Auth::user()->id,
            ]);

            if ($kelompok) {
                DB::commit();
                return ApiResponse::success('kelompok berhasil dihapus');
            } else {
                DB::rollBack();
                return ApiResponse::badRequest('kelompok gagal dihapus');
            }
        } catch (Exception $e) {
            return ApiResponse::badRequest($e->getMessage());
        }
    }

}
