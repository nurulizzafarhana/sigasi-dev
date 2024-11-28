<?php

namespace App\Http\Controllers\Api\Penduduk;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Models\Penduduk\Penduduk;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PendudukController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('role:kecamatan|posko-utama');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Mengambil daftar Penduduk dengan relasi kelompok, dan melakukan pagination
            $data_penduduk = Penduduk::with(['kelompok'])->whereNull('deleted_by')->whereNull('deleted_at');

            // pencarian berdasarkan desa
            if (isset($request->desa)) {
                $data_penduduk->where('desa', 'LIKE', '%' . $request->desa . '%'); // pencarian menggunakan type string
            }

            // pencarian berdasarkan kelompok
            if (isset($request->kelompok)) {
                $data_penduduk->where('Kelompok', $request->kelompok);
            }

            $penduduk = $data_penduduk->paginate(10);

            // Mengembalikan response sukses dengan data penduduk
            return ApiResponse::success($penduduk);
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
                'ktp' => 'nullable|string|max:16',
                'nama' => 'required|string|max:20',
                'alamat' => 'required|string|max:50',
                'desa' => 'required|string|max:20',
                'tanggal_lahir' => 'required|date',
                'jenis_kelamin' => 'required|boolean',
                'kelompok' => 'required|integer',
            ]);

            // Jika validasi gagal, kembalikan error dengan status 422
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            // Membuat data Penduduk baru dengan lock untuk mencegah konflik
            $store = Penduduk::lockForUpdate()->create([
                'KTP' => $request->ktp,
                'Nama' => $request->nama,
                'Alamat' => $request->alamat,
                'Desa' => $request->desa,
                'TanggalLahir' => $request->tanggal_lahir,
                'JenisKelamin' => $request->jenis_kelamin,
                'Kelompok' => $request->kelompok,
                'LastUpdateDate' => now(),
                'LastUpdateBy' => auth()->user()->id,
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

            $penduduk = Penduduk::with([
                'kelompok'
            ])->where('IDPenduduk', $id)->first();

            // Jika penduduk ditemukan, kembalikan response sukses
            if (!is_null($penduduk)) {
                return ApiResponse::success($penduduk);
            }

            // Jika penduduk tidak ditemukan, kembalikan response not found
            return ApiResponse::notFound();
        } catch (\Throwable $th) {
            // Tangani exception dan kembalikan error
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
                'ktp' => 'nullable|string|max:16',
                'nama' => 'required|string|max:20',
                'alamat' => 'required|string|max:50',
                'desa' => 'required|string|max:20',
                'tanggal_lahir' => 'required|date',
                'jenis_kelamin' => 'required|boolean',
                'kelompok' => 'required|integer',
            ]);

            // Jika validasi gagal, kembalikan error dengan status 422
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            // Update data Penduduk berdasarkan IDPenduduk
            $store = Penduduk::lockForUpdate()->where('IDPenduduk', $id)->update([
                'KTP' => $request->ktp,
                'Nama' => $request->nama,
                'Alamat' => $request->alamat,
                'Desa' => $request->desa,
                'TanggalLahir' => $request->tanggal_lahir,
                'JenisKelamin' => $request->jenis_kelamin,
                'Kelompok' => $request->kelompok,
                'LastUpdateDate' => now(),
                'LastUpdateBy' => auth()->user()->id,
            ]);

            // Jika update berhasil, commit transaksi dan kembalikan response sukses
            if ($store) {
                DB::commit();
                return ApiResponse::success(Penduduk::with([
                    'kelompok'
                ])->where('IDPenduduk', $id)->first());
            }

            // Rollback jika update gagal
            DB::rollback();
            return ApiResponse::badRequest();
        } catch (\Throwable $th) {
            // Rollback transaksi jika terjadi exception
            DB::rollback();
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

            $penduduk = Penduduk::where('IDPenduduk', $id)->update([
                'deleted_at' => Carbon::now(),
                'deleted_by' => Auth::user()->id,
            ]);
            if ($penduduk) {
                DB::commit();
                return ApiResponse::success('penduduk berhasil dihapus');
            } else {
                DB::rollBack();
                return ApiResponse::badRequest('penduduk gagal dihapus');
            }
        } catch (Exception $e) {
            return ApiResponse::badRequest($e->getMessage());
        }
    }
}
