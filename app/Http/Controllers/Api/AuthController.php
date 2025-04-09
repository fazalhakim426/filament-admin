<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\SupplierDetail;
use App\Trait\CustomRespone;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;

class AuthController extends Controller
{
    use CustomRespone;
    /**
     * @OA\Post(
     *     path="/register-customer",
     *     summary="Register a new customer",
     *     description="Register a new customer and assign the 'Customer' role.",
     *     tags={"Customer"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Yar", description="Customer's full name"),
     *             @OA\Property(property="email", type="string", format="email", example="customer33@manzil.comd", description="Customer's email address"),
     *             @OA\Property(property="city_id", type="string", example="1", description="City ID (optional, must exist in cities table)"),
     *             @OA\Property(property="phone", type="string", example="+123456789", description="Customer's contact number (optional)"),
     *             @OA\Property(property="whatsapp", type="string", example="+987654321", description="Customer's WhatsApp number (optional)"),
     *             @OA\Property(property="address", type="string", example="123 Customer Street", description="Customer's address (optional)"),
     *             @OA\Property(property="password", type="string", format="password", example="password123", description="Password (minimum 8 characters)"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123", description="Password confirmation")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Customer successfully registered",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Customer successfully registered"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=75),
     *                 @OA\Property(property="name", type="string", example="John Yar"),
     *                 @OA\Property(property="email", type="string", format="email", example="customer33@manzil.comd"),
     *                 @OA\Property(property="email_verified_at", type="string", format="nullable", example=null),
     *                 @OA\Property(property="roles", type="array", 
     *                     @OA\Items(type="string", example="Customer")
     *                 ),
     *                 @OA\Property(property="created_at", type="string", example="0 seconds ago"),
     *                 @OA\Property(property="updated_at", type="string", example="0 seconds ago")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation errors",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 additionalProperties=@OA\Property(type="array", @OA\Items(type="string", example="The name field is required."))
     *             )
     *         )
     *     )
     * )
     */

    public function registerCustomer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'city_id' => 'nullable|string|max:255|exists:cities,id',
            'phone' => 'nullable|string|max:25',
            'whatsapp' => 'nullable|string|max:25',
            'address' => 'nullable|string|max:255',
            'password' => 'required|string|confirmed|min:8',
        ]);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'referral_code' => Str::upper(Str::random(8)),
            'city_id' => $request->city_id,
            'phone' => $request->phone,
            'whatsapp' => $request->whatsapp,
            'address' => $request->address,

        ]);
        $user->assignRole('customer');
        return $this->json(
            200,
            true,
            'Customer successfully registered',
            new UserResource($user)
        );
    }
    public function registerSupplier(Request $request)
    {
        $request->validate([
            // User validation
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'city_id' => 'nullable|string|max:255|exists:cities,id',
            'phone' => 'nullable|string|max:25',
            'whatsapp' => 'nullable|string|max:25',
            'address' => 'nullable|string|max:255',
            // Supplier validation (all nullable)
            'business_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'website' => 'nullable|url',
            'supplier_type' => 'nullable|string|max:255',
            'main_category_id' => 'nullable|exists:categories,id',
            'secondary_category_id' => 'nullable|exists:sub_categories,id',
            'product_available' => 'nullable|integer|min:0',
            'product_source' => 'nullable|string|max:255',
            'product_unit_quality' => 'nullable|string|max:255',
            'product_range' => 'nullable|string|max:255',
            'using_daraz' => 'nullable|boolean',
            'daraz_url' => 'nullable|url',
            'ecommerce_experience' => 'nullable|string|max:255',
            'term_agreed' => 'nullable|boolean',
            'marketing_type' => 'nullable|string|max:255',
            'preferred_contact_time' => 'nullable|date',
            'cnic' => 'nullable|min:13|max:17',
            'bank_name' => 'nullable|string',
            'bank_iban' => 'nullable|string|min:10|max:24',
            'bank_account_number' => 'nullable|string|min:8|max:24',
            'bank_branch' => 'nullable|string|max:255',
            'term_of_services' => 'nullable|string|max:1000',
        ]);

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make('password'),
            'referral_code' => Str::upper(Str::random(8)),
            'city_id' => $request->city_id,
            'phone' => $request->phone,
            'whatsapp' => $request->whatsapp,
            'address' => $request->address,
        ]);
        $user->assignRole('supplier');

        // Create supplier details
        SupplierDetail::create([
            'user_id' => $user->id,
            'business_name' => $request->business_name,
            'contact_person' => $request->contact_person,
            'website' => $request->website,
            'supplier_type' => $request->supplier_type,
            'category_id' => $request->main_category_id,
            'sub_category_id' => $request->secondary_category_id,
            'product_available' => $request->product_available,
            'product_source' => $request->product_source,
            'product_unit_quality' => $request->product_unit_quality,
            'self_listing' => 0,
            'product_range' => $request->product_range,
            'using_daraz' => $request->using_daraz,
            'daraz_url' => $request->daraz_url,
            'ecommerce_experience' => $request->ecommerce_experience,
            'term_agreed' => $request->term_agreed,
            'marketing_type' => $request->marketing_type,
            'preferred_contact_time' => $request->preferred_contact_time,
            'cnic' => $request->cnic,
            'bank_name' => $request->bank_name,
            'bank_iban' => $request->bank_iban,
            'bank_branch' => $request->bank_branch,
            'bank_account_number' => $request->bank_account_number,
            'term_of_services' => $request->term_of_services,
        ]);

        return $this->json(
            201,
            true,
            'Supplier successfully registered',
            new UserResource($user->refresh()),
        );
    }

    /**
     * @OA\Post(
     *     path="/login/{role}",
     *     summary="Login a user",
     *     tags={"Authentication"},
     *     @OA\Parameter(
     *         name="role",
     *         in="path",
     *         required=true,
     *         description="Role of the user",
     *         @OA\Schema(type="string",enum={"supplier", "customer"},example="supplier")), 
     *       @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="customer@manzil.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",  
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJK...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Unauthorized: Role mismatch."))
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation errors",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function login(Request $request, $role)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|min:8',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
            if ($user->active == 0) {
                return $this->json(
                    403,
                    false,
                    'Account is inactive. Please contact support.',
                );
            }
            foreach ($user->roles as $r) {
                if (strtolower($role) === strtolower($r->name)) {
                    $token = $user->createToken($role)->plainTextToken;

                    return $this->json(
                        200,
                        true,
                        'Login successful.',
                        [
                            'token' => $token,
                            'user' => new UserResource($user)
                        ]
                    );
                }
            }
            return $this->json(
                403,
                false,
                'Unauthorized: Role mismatch.',
            );
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/forgot-password",
     *     summary="Send password reset link",
     *     description="Send a password reset link to the user's registered email address.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset link sent successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Password reset link sent!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Unable to send password reset link.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unable to send password reset link.")
     *         )
     *     )
     * )
     */

     
     public function forgotPassword(Request $request)
     {
         $request->validate([
             'email' => 'required|email|exists:users,email',
         ]);
     
         // Generate a 5-digit random code
         $resetCode = random_int(10000, 99999);
     
         // Store code in password_resets table
         DB::table('password_resets')->updateOrInsert(
             ['email' => $request->email],
             ['token' => Hash::make($resetCode), 'created_at' => now()]
         );
     
         // Send code via email
         Mail::raw("Your password reset code is: $resetCode", function ($message) use ($request) {
             $message->to($request->email)
                 ->subject('Password Reset Code');
         });
     
         return $this->json(200, true, 'Password reset code sent!');
     }
     
 
    public function resetPassword(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed', // Ensure the password is confirmed
        ]);

        if ($validator->fails()) {
            return $this->json(422, false, 'Validation error', [], $validator->errors());
        }

        // Attempt to reset the password using the token
        $response = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                // Set the new password
                $user->forceFill([
                    'password' => Hash::make($request->password),
                ])->save();

                // Fire the PasswordReset event (optional)
                event(new PasswordReset($user));
            }
        );

        // Check the response status
        if ($response == Password::PASSWORD_RESET) {
            return $this->json(200, true, 'Password has been successfully reset.');
        }

        return $this->json(400, false, 'The provided token is invalid or expired.');
    }

    public function resetPasswordAuth(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6|confirmed', // Ensure the password is confirmed
        ]);

        if ($validator->fails()) {
            return $this->json(422, false, 'Validation error', [], $validator->errors());
        }

        // Attempt to reset the password using the token
        $user = Auth::user();
        $user->update([

            'password' => Hash::make($request->password),
        ]);
        event(new PasswordReset($user));
        return $this->json(200, true, 'Password has been successfully reset.');
    }
}
