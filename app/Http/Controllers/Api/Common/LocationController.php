<?php

namespace App\Http\Controllers\Api\Common;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use Illuminate\Http\Request;

use App\Trait\CustomRespone;
class LocationController extends Controller
{
    use CustomRespone;
    public function getCountries()
    {
        $countries = Country::all();
        return $this->json(200,true,'list',  $countries);
    }

    public function getStatesByCountry($country_id)
    {
        $states = State::where('country_id', $country_id)->get();
        return $this->json(200,true,'list',  $states);
    }

    public function getCitiesByState($state_id)
    {
        $cities = City::where('state_id', $state_id)->get();
        return $this->json(200,true,'list',  $cities);
    }
}
