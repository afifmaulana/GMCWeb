<?php

namespace App\Http\Controllers\v1;

use App\DataFilm;
use App\Http\Resources\FilmResource;
use App\Http\Resources\JadwalTayangResource;
use App\JadwalTayang;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FilmController extends Controller
{
    public function film()
    {
        try{
            $films = DataFilm::all();

            return response()->json([
                'message' => 'success',
                'status' => true,
                'data' => FilmResource::collection($films)
            ]);
        }catch (\Exception $exception){
            return response()->json([
                'message' => $exception->getMessage(),
                'status' => false,
                'data' => (object)[]
            ]);
        }
    }

    public function filmnowplaying()
    {
        try{

            $films  = DataFilm::with(['jadwaltayang' => function($query){
                $query->where('tanggal_mulai' ,'<=', Carbon::now())
                    ->where('tanggal_selesai', '>=', Carbon::now());
            }])->get();

            $results = [];
            foreach ($films as $film){
                if ($film->jadwaltayang){
                    array_push($results, $film);
                }
            }

            return response()->json([
                'message' => 'successfully get film now playing',
                'status' => true,
                'data' => FilmResource::collection(collect($results))
            ]);


        }catch (\Exception $exception){
            return response()->json([
                'message' => $exception->getMessage(),
                'status' => false
            ]);
        }
    }

    public function filmcomingsoon()
    {
        try{

            $films  = DataFilm::with(['jadwaltayang' => function($query){
                $query->where('tanggal_mulai' ,'>=', Carbon::now())
                ->OrWhere('tanggal_selesai', '<=', Carbon::now());
            }])->where('status', '1')->get();

            $results = [];
            foreach ($films as $film){
                if ($film->jadwaltayang){
                    array_push($results, $film);
                }
            }
            return response()->json([
                'message' => 'successfully get film coming soon',
                'status' => true,
                'data' => FilmResource::collection(collect($results))
            ]);

        }catch (\Exception $exception){
            return response()->json([
                'message' => $exception->getMessage(),
                'status' => false,
                'data' => (object)[]
            ]);
        }
    }
}