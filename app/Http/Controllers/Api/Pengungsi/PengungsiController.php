<?php

namespace App\Http\Controllers\Api\Pengungsi;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Models\Penduduk\Penduduk;
use App\Models\Pengungsi\Pengungsi;
use App\Models\Posko\Posko;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PengungsiController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('role:posko-utama|posko');
    }

    public function index(Request $request)
    {
        // menampilkan seluruh data pengungsi dengan dibatasi per 10 data
        $data_pengungsi = Pengungsi::with(['penduduk.kelompok', 'posko.user']);

        // pencarian berdasarkan id posko
        if (isset($request->posko)) {
            $data_pengungsi->where('IDPosko', $request->posko);
        }

        // pencarian berdasarkan id kelompok
        if (isset($request->kelompok)) {
            $data_pengungsi->whereHas('penduduk', function ($query) use ($request) {
                $query->where('Kelompok', $request->kelompok);
            });
        }

        $pengungsi = $data_pengungsi->paginate(10);

        return ApiResponse::success($pengungsi);
    }

    public function show($id)
    {
        // menampilkan detail pengungsi, degan relasi pendusuk, posko dan user
        $pengungsi = Pengungsi::with(['penduduk', 'posko.user'])->where('IDPengungsi', $id)->first();

        if (is_null($pengungsi)) {
            return ApiResponse::badRequest('Data pengungsi tidak ditemukan.');
        }

        return ApiResponse::success($pengungsi);
    }

    public function createOrEdit()
    {
        try {

            $penduduk = Penduduk::whereNull('deleted_by')->whereNull('deleted_at')->get();
            $posko = Posko::whereNull('deleted_by')->whereNull('deleted_at')->get();

            return ApiResponse::success([
                'penduduk' => $penduduk,
                'posko' => $posko
            ]);
        } catch (\Throwable $th) {

            return ApiResponse::badRequest($th->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [ // validasi parameter
                'idPosko' => 'required|numeric',
                'idPenduduk' => 'required|numeric',
                'condition' => 'string|max:255',
            ]);

            if ($validator->fails()) { // jika parameter ada yang tidak sesuai dengan aturan, maka masuk kondisi error
                return ApiResponse::badRequest($validator->errors());
            }

            $posko = Posko::where('IDPosko', $request->idPosko)->first();
            $user = User::where('id', $request->idPenduduk)->first();

            if (!$posko) { // cek apakah posko ada, jika tidak ada maka return error
                return ApiResponse::badRequest('posko tidak ditemkan');
            }

            if (!$user) { // cek apakah user ada, jika tidak ada maka return error
                return ApiResponse::badRequest('user tidak ditemkan');
            }

            DB::beginTransaction(); // memulai transaksi
            $pengungsi = Pengungsi::lockForUpdate()->create([ // membuat record baru
                'IDPenduduk' => $request->idPenduduk, // ini adalah id user
                'IDPosko' => $request->idPosko,
                'KondisiKhusus' => $request->condition,
                'LastUpdateDate' => Carbon::now(),
                'LastUpdateBy' => Auth::user()->id,
            ]);

            if ($pengungsi) {
                DB::commit();
                return ApiResponse::created($pengungsi);
            } else {
                DB::rollBack();
                return ApiResponse::badRequest('Data pengungsi tidak dapat disimpan.');
            }
        } catch (Exception $e) {
            return ApiResponse::badRequest($e);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [ // cek validasi
                'idPosko' => 'required|numeric',
                'idPenduduk' => 'required|numeric',
                'condition' => 'string|max:255',
            ]);

            if ($validator->fails()) { // jika ada validasi yang tidak sesuai maka return error
                return ApiResponse::badRequest($validator->errors());
            }

            $posko = Posko::where('IDPosko', $request->idPosko)->first();
            $user = User::where('id', $request->idPenduduk)->first();

            if (!$posko) { // jika posko tidak ada maka retur error
                return ApiResponse::badRequest('posko tidak ditemkan');
            }

            if (!$user) { // jika user tidak ada maka muncul error
                return ApiResponse::badRequest('user tidak ditemkan');
            }

            DB::beginTransaction(); // memulai transaksi
            $pengungsi = Pengungsi::where('IDPengungsi', $id)->lockForUpdate()->update([ // update record berdasarkan id pengungsi
                'IDPenduduk' => $request->idPenduduk,
                'IDPosko' => $request->idPosko,
                'KondisiKhusus' => $request->condition,
                'LastUpdateDate' => Carbon::now(),
                'LastUpdateBy' => Auth::user()->id,
            ]);
            if ($pengungsi) {
                DB::commit();
                $data_pengungsi = Pengungsi::with(['penduduk', 'posko.user'])->where('IDPengungsi', $id)->first();
                return ApiResponse::success($data_pengungsi);
            } else {
                DB::rollBack();
                return ApiResponse::badRequest('pengungsi tidak ditemukan');
            }
        } catch (Exception $e) {
            return ApiResponse::badRequest($e);
        }
    }

    public function delete($id)
    {
        try {
            DB::beginTransaction();

            $pengungsi = Pengungsi::where('IDPengungsi', $id)->update([
                'deleted_at' => Carbon::now(),
                'deleted_by' => Auth::user()->id,
            ]);
            if ($pengungsi) {
                DB::commit();
                return ApiResponse::success('pengungsi berhasil dihapus');
            } else {
                DB::rollBack();
                return ApiResponse::badRequest('pengungsi gagal dihapus');
            }
        } catch (Exception $e) {
            return ApiResponse::badRequest($e->getMessage());
        }
    }
}
