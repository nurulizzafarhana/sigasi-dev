<?php

namespace App\Http\Controllers\Api\Donatur;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Models\Donatur\Donatur;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DonaturController extends Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->middleware('role:kecamatan')->only('index');
        $this->middleware('role:bansos|kecamatan');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Mengambil data donatur dengan paginasi
            $donatur = Donatur::whereNull('deleted_by')->whereNull('deleted_at')->paginate(10); // Membatasi hasil menjadi 10 per halaman

            // Mengembalikan respons sukses dengan data donatur yang dipaginasi
            return ApiResponse::success($donatur);
        } catch (\Throwable $th) {
            // Menangkap dan menampilkan pesan error jika terjadi kesalahan
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
                'nama_perusahaan' => 'nullable|string|max:50', // 'nama_perusahaan' bersifat opsional, tipe string, maksimal 50 karakter
                'alamat' => 'required|string|max:255', // 'alamat' wajib, tipe string, maksimal 255 karakter
                'nomor_kontak' => 'required|string|max:16', // 'nomor_kontak' wajib, tipe string, maksimal 16 karakter
            ]);

            // Jika validasi gagal, kembalikan respons error
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422); // Mengembalikan error validasi dengan kode 422
            }

            // Membuat entri baru pada tabel 'Donatur' menggunakan data dari request
            $donatur = Donatur::lockForUpdate()->create([
                'NamaPerusahaan' => $request->nama_perusahaan, // Menyimpan 'nama_perusahaan' dari request
                'Alamat' => $request->alamat, // Menyimpan 'alamat' dari request
                'NomorKontak' => $request->nomor_kontak, // Menyimpan 'nomor_kontak' dari request
                'LastUpdateDate' => now(), // Menyimpan tanggal update terakhir
                'LastUpdateBy' => Auth::user()->id, // Menyimpan ID user yang melakukan update
            ]);

            // Jika data donatur berhasil disimpan
            if ($donatur) {
                // Komit transaksi
                DB::commit();
                return ApiResponse::created($donatur); // Mengembalikan respons sukses dengan data donatur yang dibuat
            }

            // Jika gagal, rollback transaksi
            DB::rollback();
            return ApiResponse::badRequest(); // Mengembalikan respons error
        } catch (\Throwable $th) {
            // Rollback transaksi jika terjadi exception
            DB::rollback();
            return ApiResponse::badRequest($th->getMessage()); // Mengembalikan error dengan pesan exception
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            // Mencari data donatur berdasarkan IDDonatur
            $donatur = Donatur::where('IDDonatur', $id)->first();

            // Jika data donatur ditemukan
            if (!is_null($donatur)) {
                return ApiResponse::success($donatur); // Mengembalikan respons sukses dengan data donatur
            }

            // Jika data donatur tidak ditemukan
            return ApiResponse::notFound(); // Mengembalikan respons not found
        } catch (\Throwable $th) {
            // Menangkap exception dan mengembalikan pesan error
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
                'nama_perusahaan' => 'nullable|string|max:50', // nama_perusahaan bersifat opsional, maksimal 50 karakter
                'alamat' => 'required|string|max:255', // alamat wajib, maksimal 255 karakter
                'nomor_kontak' => 'required|string|max:16', // nomor_kontak wajib, maksimal 16 karakter
            ]);

            // Jika validasi gagal, kembalikan respons error
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422); // Mengembalikan error validasi dengan kode 422
            }

            // Mengupdate data donatur di tabel berdasarkan ID yang diberikan
            $donatur = Donatur::lockForUpdate()->where('IDDonatur', $id)->update([
                'NamaPerusahaan' => $request->nama_perusahaan, // Mengupdate nama perusahaan
                'Alamat' => $request->alamat, // Mengupdate alamat
                'NomorKontak' => $request->nomor_kontak, // Mengupdate nomor kontak
                'LastUpdateDate' => now(), // Mengupdate waktu terakhir diperbarui
                'LastUpdateBy' => Auth::user()->id, // Mengupdate user yang memperbarui data
            ]);

            // Jika proses update berhasil
            if ($donatur) {
                DB::commit(); // Menyimpan perubahan ke database
                // Mengembalikan respons sukses dengan data donatur terbaru
                return ApiResponse::success(Donatur::where('IDDonatur', $id)->first());
            }

            // Jika update gagal, rollback transaksi
            DB::rollback();
            return ApiResponse::badRequest(); // Mengembalikan respons bad request
        } catch (\Throwable $th) {
            // Rollback transaksi jika terjadi exception
            DB::rollback();
            return ApiResponse::badRequest($th->getMessage()); // Mengembalikan error dengan pesan exception
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete($id)
    {
        try {
            DB::beginTransaction();

            $donatur = Donatur::where('IDDonatur', $id)->update([
                'deleted_at' => Carbon::now(),
                'deleted_by' => Auth::user()->id,
            ]);
            if ($donatur) {
                DB::commit();
                return ApiResponse::success('donatur berhasil dihapus');
            } else {
                DB::rollBack();
                return ApiResponse::badRequest('donatur gagal dihapus');
            }
        } catch (Exception $e) {
            return ApiResponse::badRequest($e->getMessage());
        }
    }
}
