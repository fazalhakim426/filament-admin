<?php

namespace App\Http\Controllers\Api\Common;

use App\Http\Controllers\Controller;
use App\Http\Resources\AddressResource;
use App\Trait\CustomRespone;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressesController extends Controller
{
    use CustomRespone;
    public function index()
    {
        $user = Auth::user();
        return $this->json(200, true, 'Address list!', AddressResource::collection($user->addresses));
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'address'     => 'required|string',
            'name'        => 'required|string',
            'email'       => 'required|email',
            'phone'       => 'required|string',
            'near_by'     => 'required|string',
            'whatsapp'    => 'required|string',
            'street'      => 'required|string',
            'zip'         => 'required|string',
            'country_id'  => 'required|exists:countries,id',
            'state_id'    => 'required|exists:states,id',
            'city_id'     => 'required|exists:cities,id',
        ]);

        $address = Address::create($validatedData + [
            'user_id' => Auth::id(),
        ]);

        return $this->json(200, true, 'Address created successfully!', new AddressResource($address));
    }
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'address'     => 'required|string',
            'name'        => 'required|string',
            'email'       => 'required|email',
            'phone'       => 'required|string',
            'near_by'     => 'required|string',
            'whatsapp'    => 'required|string',
            'street'      => 'required|string',
            'zip'         => 'required|string',
            'country_id'  => 'required|exists:countries,id',
            'state_id'    => 'required|exists:states,id',
            'city_id'     => 'required|exists:cities,id',
        ]);

        $address = Address::findOrFail($id);

        // Optional: Authorize that the user owns this address
        if ($address->user_id !== Auth::id()) {
            return $this->json(403, false, 'You are not authorized to update this address.');
        }

        $address->update($validatedData);

        return $this->json(200, true, 'Address updated successfully!', new AddressResource($address));
    }

    public function show(Address $address)
    {
        return $this->json(
            200,
            true,
            'Address show !',
            new AddressResource($address)
        );
    }
    public function destroy(Address $address)
    {
        $user = Auth::user();
        if ($address->user_id != $user->id) {
            return $this->json(422, false, 'Address not belong to you');
        }
        $address->delete();
        return $this->json(200, true, 'Address delete successfully!');
    }
}
