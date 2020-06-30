<?php

namespace App\Http\Controllers\admin;

use App\DataFilm;
use App\JadwalTayang;
use App\Studio;
use App\TanggalTayang;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JadwalTayangController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        $datas = JadwalTayang::all();
        return view('pages.admin.jadwal_tayang.index', compact('datas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = JadwalTayang::all();
        $datafilms = DataFilm::where('status','1')->orWhere('status','2')->get();

        $studios = Studio::all();
        return view('pages.admin.jadwal_tayang.create', compact('data', 'datafilms', 'studios'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $startDate = strtotime($request->start);
        $endDate = strtotime($request->end);
        //$start = date('Y-m-d', $startDate);
        //$end = date('Y-m-d', $endDate);

        $startDay = date('d', $startDate);



        $startMonth = date('m', $startDate);
        $startYear = date('Y', $startDate);

        $endDay = date('d', $endDate);
        $endMonth = date('m', $endDate);
        $endYear = date('Y', $endDate);

        $jadwalTayang = new JadwalTayang();
        $jadwalTayang->id_film= $request->id_film;
        $jadwalTayang->id_studio = $request->id_studio;
        $jadwalTayang->harga = $request->harga;
        $jadwalTayang->save();

        if ($startMonth == $endMonth){
            while ($startDay <= $endDay){
                $date_id = TanggalTayang::latest('id')->pluck('id')->first();

                $itemDate = [
                    'id' => $date_id == null ? 1 : $date_id + 1,
                    'id_film' => $jadwalTayang->id_film,
                    'id_studio' => $jadwalTayang->id_studio,
                    'id_jadwal_tayang' => $jadwalTayang->id,
                    'tanggal' => $startYear.'-'.$startMonth.'-'.$startDay
                ];
                TanggalTayang::create($itemDate);

                $hours = $request->jam_tayang;
                foreach ($hours as $hour) {
                    $itemHour[] = [
                        'id_film' => $jadwalTayang->id_film,
                        'id_studio' => $jadwalTayang->id_studio,
                        'id_jadwal_tayang' => $jadwalTayang->id,
                        'id_tanggal_tayang' => $itemDate['id'],
                        'jam' => $hour
                    ];
                };
                $startDay++;
            }

            DB::table('jam_tayangs')->insert($itemHour);
        }else{
            $startDayEndOfMonth = Carbon::now()->month($startMonth)->endOfMonth()->format('d');
            while ($startDay <= $startDayEndOfMonth){
                $date_id = TanggalTayang::latest('id')->pluck('id')->first();

                $itemDate = [
                    'id' => $date_id == null ? 1 : $date_id + 1,
                    'id_film' => $jadwalTayang->id_film,
                    'id_studio' => $jadwalTayang->id_studio,
                    'id_jadwal_tayang' => $jadwalTayang->id,
                    'tanggal' => $startYear.'-'.$startMonth.'-'.$startDay
                ];
                TanggalTayang::create($itemDate);

                $hours = $request->jam_tayang;
                foreach ($hours as $hour) {
                    $itemHour[] = [
                        'id_film' => $jadwalTayang->id_film,
                        'id_studio' => $jadwalTayang->id_studio,
                        'id_jadwal_tayang' => $jadwalTayang->id,
                        'id_tanggal_tayang' => $itemDate['id'],
                        'jam' => $hour
                    ];
                };
                $startDay++;
            }
            DB::table('jam_tayangs')->insert($itemHour);

            $newStartDay = 1;
            while ($newStartDay <= $endDay){
                $date_id = TanggalTayang::latest('id')->pluck('id')->first();

                $itemDate = [
                    'id' => $date_id == null ? 1 : $date_id + 1,
                    'id_film' => $jadwalTayang->id_film,
                    'id_studio' => $jadwalTayang->id_studio,
                    'id_jadwal_tayang' => $jadwalTayang->id,
                    'tanggal' => $startYear.'-'.$endMonth.'-'.$newStartDay
                ];
                TanggalTayang::create($itemDate);

                $hours = $request->jam_tayang;
                foreach ($hours as $hour) {
                    $itemHour[] = [
                        'id_film' => $jadwalTayang->id_film,
                        'id_studio' => $jadwalTayang->id_studio,
                        'id_jadwal_tayang' => $jadwalTayang->id,
                        'id_tanggal_tayang' => $itemDate['id'],
                        'jam' => $hour
                    ];
                };
                $newStartDay++;
            }
            DB::table('jam_tayangs')->insert($itemHour);
        }

        return redirect()->route('jadwal_tayang.index')->with('create', 'Berhasil Menambahkan Data');
}

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = JadwalTayang::find($id);
        return view('pages.admin.jadwal_tayang.show', compact('data'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = JadwalTayang::find($id);
        $datafilms = DataFilm::whereIn('status', ['1', '2'])->get();
        $studios = Studio::all();

        return view('pages.admin.jadwal_tayang.edit', compact('data', 'datafilms', 'studios'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $this->validate($request, [
            'jam_tayang' => 'required',
            'tgl_awal' => 'required',
            'tgl_akhir' => 'required',
            'harga' => 'required',

        ]);

        //dd(['sebelum '=> $request->tanggal_mulai, 'sesudah' => Carbon::parse($request->tanggal_mulai)->format('Y-m-d')]);


        /*$start = date('Y-m-d', strtotime($request->tanggal_mulai));
        $end = date('Y-m-d', strtotime($request->tanggal_selesai));*/


        $data = JadwalTayang::find($id);
        $data->id_film= $request->id_film;
        $data->id_studio = $request->id_studio;
        $data->harga = $request->harga;
        $data->jam_tayang =  implode(',',$request->jam_tayang);
        $data->tanggal_mulai = $request->tgl_awal;
        $data->tanggal_selesai = $request->tgl_akhir;
        $data->update();

        return redirect()->route('jadwal_tayang.index');


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = JadwalTayang::find($id);
        $data->delete();
        return redirect()->route('jadwal_tayang.index')->with('dalete', 'Berhasil Menghapus Data');
    }
}
