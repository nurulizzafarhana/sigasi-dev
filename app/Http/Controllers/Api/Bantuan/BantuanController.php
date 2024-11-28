<?php

namespace App\Http\Controllers\Api\Bantuan;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Models\Bantuan\Bantuan;
use App\Models\Bantuan\Bantuan_Dtl;
use App\Models\Barang\Barang;
use App\Models\Donatur\Donatur;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BantuanController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('role:kecamatan|posko-utama');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Mengambil data bantuan beserta relasinya
            $bantuan = Bantuan::whereNull('deleted_by')->whereNull('deleted_at')->with([
                'donatur', // Memuat relasi 'donatur'
                'bantuanDetail.barang' // Memuat relasi 'bantuanDetail' dan 'barang'
            ])
                ->orderBy('IDBantuan', 'desc') // Mengurutkan data berdasarkan IDBantuan secara menurun
                ->paginate(10); // Menggunakan pagination untuk membatasi data menjadi 10 per halaman

            // Mengembalikan respons sukses dengan data bantuan yang dipaginasi
            return ApiResponse::success($bantuan);
        } catch (\Throwable $th) {
            // Menangkap dan menampilkan pesan error jika terjadi kesalahan
            return ApiResponse::badRequest($th->getMessage());
        }
    }

    public function createOrEdit()
    {
        try {

            $donatur = Donatur::whereNull('deleted_by')->whereNull('deleted_at')->get();
            $barang = Barang::whereNull('deleted_by')->whereNull('deleted_at')->get();

            return ApiResponse::success([
                'donatur' => $donatur,
                'barang' => $barang
            ]);
        } catch (\Throwable $th) {

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

            // Validasi data yang diterima dari request
            $validator = Validator::make($request->all(), [
                'donatur' => 'integer|exists:donatur,IDDonatur', // Memastikan bahwa 'donatur' adalah integer dan ada dalam tabel 'donatur' berdasarkan 'IDDonatur'
                'tanggal_bantuan' => 'date|nullable', // Memastikan 'tanggal_bantuan' adalah tanggal yang valid, boleh null
            ]);

            // Jika validasi gagal, kembalikan respons error
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422); // Menampilkan error validasi dengan status code 422
            }

            // Membuat entri baru di tabel 'bantuan' dengan data dari request
            $bantuan = Bantuan::lockForUpdate()->create([
                'IDDonatur' => $request->donatur, // Menyimpan ID donatur
                'TanggalBantuan' => $request->tanggal_bantuan, // Menyimpan tanggal bantuan
                'LastUpdateDate' => now(), // Menyimpan waktu update terakhir
                'LastUpdateBy' => Auth::user()->id, // Menyimpan ID user yang melakukan update terakhir
            ]);

            // Jika proses penyimpanan bantuan berhasil
            if ($bantuan) {

                // Melakukan iterasi untuk setiap barang yang diterima dari request
                foreach ($request->barang as $item) {

                    // Menyimpan detail bantuan di tabel 'Bantuan_Dtl'
                    $bantuan_detail = Bantuan_Dtl::lockForUpdate()->insert([
                        'IDBantuan' => $bantuan->IDBantuan, // Menyimpan ID bantuan
                        'IDBarang' => $item['id_barang'], // Menyimpan ID barang
                        'Jumlah' => $item['jumlah_barang'], // Menyimpan jumlah barang
                    ]);
                }

                // Jika proses penyimpanan detail bantuan berhasil
                if ($bantuan_detail) {

                    // Komit transaksi, simpan perubahan ke database
                    DB::commit();
                    return ApiResponse::created($bantuan); // Mengembalikan respons sukses
                } else {

                    // Rollback transaksi jika ada error dalam penyimpanan detail bantuan
                    DB::rollback();
                    return ApiResponse::badRequest(); // Mengembalikan respons error
                }
            }

            // Rollback transaksi jika penyimpanan bantuan gagal
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
            // Mengambil data 'bantuan' berdasarkan 'IDBantuan' yang diberikan
            // Menggunakan eager loading untuk memuat relasi 'donatur' dan 'bantuanDetail.barang' secara bersamaan
            $bantuan = Bantuan::with([
                'donatur', // Memuat relasi 'donatur'
                'bantuanDetail.barang' // Memuat relasi 'bantuanDetail' dan relasi turunan 'barang'
            ])
                ->where('IDBantuan', $id) // Filter data berdasarkan IDBantuan
                ->first(); // Mengambil record pertama yang cocok

            // Memeriksa apakah data 'bantuan' ditemukan
            if (!is_null($bantuan)) {
                // Jika ditemukan, kembalikan respons sukses dengan data 'bantuan'
                return ApiResponse::success($bantuan);
            }

            // Jika data 'bantuan' tidak ditemukan, kembalikan respons 'not found'
            return ApiResponse::notFound();
        } catch (\Throwable $th) {
            // Jika terjadi error selama proses, tangkap exception dan kembalikan respons bad request dengan pesan error
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
                'donatur' => 'integer|exists:donatur,IDDonatur', // Memastikan 'donatur' adalah integer dan ada di tabel 'donatur'
                'tanggal_bantuan' => 'date|nullable', // Memastikan 'tanggal_bantuan' adalah tanggal yang valid, boleh null
            ]);

            // Jika validasi gagal, kembalikan respons error
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422); // Menampilkan error validasi
            }

            // Mengupdate data bantuan di tabel 'bantuan' berdasarkan ID yang diberikan
            $bantuan = Bantuan::lockForUpdate()->where('IDBantuan', $id)->update([
                'IDDonatur' => $request->donatur, // Update ID donatur
                'TanggalBantuan' => $request->tanggal_bantuan, // Update tanggal bantuan
                'LastUpdateDate' => now(), // Update waktu terakhir diubah
                'LastUpdateBy' => Auth::user()->id, // Update user yang terakhir mengubah data
            ]);

            // Jika update berhasil
            if ($bantuan) {
                // Mengambil detail bantuan lama dari tabel 'Bantuan_Dtl'
                $old_bantuan_detail = Bantuan_Dtl::where('IDBantuan', $id)->get()->pluck('IDBarang')->toArray();
                // Mengambil detail bantuan baru dari request
                $new_bantuan_detail = collect($request->barang)->pluck('id_barang')->toArray();

                // Barang yang perlu ditambahkan (tidak ada di data lama)
                $to_insert = array_diff($new_bantuan_detail, $old_bantuan_detail);
                // Barang yang perlu di-update (ada di data lama dan baru)
                $to_update = array_intersect($old_bantuan_detail, $new_bantuan_detail);
                // Barang yang perlu dihapus (hanya ada di data lama)
                $to_delete = array_diff($old_bantuan_detail, $new_bantuan_detail);

                // Hapus barang yang tidak ada di request baru
                if (!empty($to_delete)) {
                    Bantuan_Dtl::lockForUpdate()->where('IDBantuan', $id)
                        ->whereIn('IDBarang', $to_delete)
                        ->delete();
                }

                // Tambahkan barang baru yang tidak ada di data lama
                foreach ($request->barang as $item) {
                    if (in_array($item['id_barang'], $to_insert)) {
                        Bantuan_Dtl::lockForUpdate()->insert([
                            'IDBantuan' => $id,
                            'IDBarang' => $item['id_barang'],
                            'Jumlah' => $item['jumlah_barang'],
                        ]);
                    }
                }

                // Update jumlah barang yang sudah ada di data lama dan baru
                foreach ($request->barang as $item) {
                    if (in_array($item['id_barang'], $to_update)) {
                        Bantuan_Dtl::lockForUpdate()->where('IDBantuan', $id)
                            ->where('IDBarang', $item['id_barang'])
                            ->update([
                                'Jumlah' => $item['jumlah_barang']
                            ]);
                    }
                }

                // Komit transaksi jika semua proses berhasil
                DB::commit();

                // Mengembalikan respons sukses dengan data bantuan terbaru
                return ApiResponse::success(
                    Bantuan::with([
                        'donatur', // Menampilkan relasi donatur
                        'bantuanDetail.barang' // Menampilkan relasi bantuanDetail dan barang
                    ])->where('IDBantuan', $id)->first()
                );
            }

            // Jika update data bantuan gagal, rollback transaksi
            DB::rollback();
            return ApiResponse::badRequest();
        } catch (\Throwable $th) {
            // Rollback transaksi jika terjadi exception
            DB::rollback();
            return ApiResponse::badRequest([
                'file' => $th->getFile(), // Menyertakan file yang menyebabkan error
                'message' => $th->getMessage(), // Menyertakan pesan error
                'line' => $th->getLine(), // Menyertakan baris yang menyebabkan error
            ]);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function delete($id)
    {
        try {
            DB::beginTransaction();

            $bantuan = Bantuan::where('IDBantuan', $id)->update([
                'deleted_at' => Carbon::now(),
                'deleted_by' => Auth::user()->id,
            ]);
            if ($bantuan) {
                DB::commit();
                return ApiResponse::success('bantuan berhasil dihapus');
            } else {
                DB::rollBack();
                return ApiResponse::badRequest('bantuan gagal dihapus');
            }
        } catch (Exception $e) {
            return ApiResponse::badRequest($e->getMessage());
        }
    }
}
