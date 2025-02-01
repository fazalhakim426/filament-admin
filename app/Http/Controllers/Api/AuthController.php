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
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;

class AuthController extends Controller
{
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
        return response()->json([
            'message' => 'Customer successfully registered',
            'user' => new UserResource($user)
        ]);
    }
    /**
     * @OA\Post(
     *     path="/register-supplier",
     *     summary="Register a new supplier",
     *     description="This endpoint registers a new supplier with both user and supplier-specific details.",
     *     tags={"Supplier"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", description="User's full name", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", description="User's email address", example="john.doe@example.com"),
     *             @OA\Property(property="city_id", type="string", nullable=true, description="City ID associated with the user", example="1"),
     *             @OA\Property(property="phone", type="string", nullable=true, description="Contact number of the user", example="123456789"),
     *             @OA\Property(property="whatsapp", type="string", nullable=true, description="WhatsApp number of the user", example="987654321"),
     *             @OA\Property(property="address", type="string", nullable=true, description="Address of the user", example="123 Main Street"),
     *             @OA\Property(property="password", type="string", description="User's password (min: 8 characters)", example="securepassword123"),
     *             @OA\Property(property="password_confirmation", type="string", description="Confirmation of the password", example="securepassword123"),
     *             @OA\Property(property="business_name", type="string", description="Supplier's business name", example="John Supplies"),
     *             @OA\Property(property="contact_person", type="string", nullable=true, description="Contact person for the supplier", example="John Contact"),
     *             @OA\Property(property="website", type="string", format="url", nullable=true, description="Supplier's website", example="https://johnsupplies.com"),
     *             @OA\Property(property="supplier_type", type="string", nullable=true, description="Type of supplier", example="Retail"),
     *             @OA\Property(property="category_id", type="integer", nullable=true, description="ID of the main product category", example=5),
     *             @OA\Property(property="sub_category_id", type="integer", nullable=true, description="ID of the secondary product category", example=10),
     *             @OA\Property(property="product_available", type="integer", nullable=true, description="Quantity of products available", example=100),
     *             @OA\Property(property="product_source", type="string", nullable=true, description="Source of the products", example="Local"),
     *             @OA\Property(property="product_unit_quality", type="string", nullable=true, description="Quality of the product units", example="High"),
     *             @OA\Property(property="self_listing", type="boolean", nullable=true, description="Indicates if the supplier lists products themselves", example=false),
     *             @OA\Property(property="product_range", type="string", nullable=true, description="Range of products offered", example="Wide"),
     *             @OA\Property(property="using_daraz", type="boolean", nullable=true, description="Indicates if the supplier uses Daraz for selling", example=false),
     *             @OA\Property(property="daraz_url", type="string", format="url", nullable=true, description="Daraz profile URL", example="https://daraz.pk/johnsupplies"),
     *             @OA\Property(property="ecommerce_experience", type="string", nullable=true, description="Supplier's e-commerce experience", example="5 years"),
     *             @OA\Property(property="term_agreed", type="boolean", nullable=true, description="Indicates if the supplier has agreed to terms", example=true),
     *             @OA\Property(property="marketing_type", type="string", nullable=true, description="Type of marketing used by the supplier", example="Online"),
     *             @OA\Property(property="preferred_contact_time", type="string", format="datetime", nullable=true, description="Preferred time for contact", example="2025-01-25 10:00:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Supplier successfully registered",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Supplier successfully registered"),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=74),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="fff@rrrrr.ffddje"),
     *                 @OA\Property(property="email_verified_at", type="string", nullable=true, example=null),
     *                 @OA\Property(property="roles", type="array", @OA\Items(type="string"), example={"Supplier"}),
     *                 @OA\Property(property="created_at", type="string", example="0 seconds ago"),
     *                 @OA\Property(property="updated_at", type="string", example="0 seconds ago"),
     *                 @OA\Property(
     *                     property="supplier_detail",
     *                     type="object",
     *                     @OA\Property(property="business_name", type="string", example="John Supplies"),
     *                     @OA\Property(property="contact_person", type="string", example="John Contact"),
     *                     @OA\Property(property="website", type="string", example="https://johnsupplies.com"),
     *                     @OA\Property(property="supplier_type", type="string", example="Retail"),
     *                     @OA\Property(property="category_id", type="integer", example=5),
     *                     @OA\Property(property="sub_category_id", type="integer", example=10),
     *                     @OA\Property(property="product_available", type="integer", example=100),
     *                     @OA\Property(property="product_source", type="string", example="Local"),
     *                     @OA\Property(property="product_unit_quality", type="string", example="High"),
     *                     @OA\Property(property="self_listing", type="boolean", example=false),
     *                     @OA\Property(property="product_range", type="string", example="Wide"),
     *                     @OA\Property(property="using_daraz", type="boolean", example=false),
     *                     @OA\Property(property="daraz_url", type="string", example="https://daraz.pk/johnsupplies"),
     *                     @OA\Property(property="ecommerce_experience", type="string", example="5 years"),
     *                     @OA\Property(property="term_agreed", type="boolean", example=true),
     *                     @OA\Property(property="marketing_type", type="string", example="Online"),
     *                     @OA\Property(property="preferred_contact_time", type="string", format="datetime", example="2025-01-25 10:00:00")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
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
            'category_id' => 'nullable|exists:categories,id',
            'sub_category_id' => 'nullable|exists:categories,id',
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
            'category_id' => $request->category_id,
            'sub_category_id' => $request->sub_category_id,
            'product_available' => $request->product_available,
            'product_source' => $request->product_source,
            'product_unit_quality' => $request->product_unit_quality,
            'self_listing' => 0,
            'supplier_request' => 0,
            'product_range' => $request->product_range,
            'using_daraz' => $request->using_daraz,
            'daraz_url' => $request->daraz_url,
            'ecommerce_experience' => $request->ecommerce_experience,
            'term_agreed' => $request->term_agreed,
            'marketing_type' => $request->marketing_type,
            'preferred_contact_time' => $request->preferred_contact_time,
        ]);

        return response()->json([
            'message' => 'Supplier successfully registered',
            'user' => new UserResource($user->refresh()),
        ]);
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
                return response()->json([
                    'message' => 'Account is inactive. Please contact support.',
                ], 403);
            }
            foreach ($user->roles as $r) {
                if (strtolower($role) === strtolower($r->name)) {
                    $token = $user->createToken($role)->plainTextToken;
                    return response()->json([
                        'message' => 'Login successful',
                        'token' => $token
                    ]);
                }
            }
            return response()->json([
                'message' => 'Unauthorized: Role mismatch.',
            ], 403);
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

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Password reset link sent!']);
        }

        return response()->json(['message' => 'Unable to send password reset link.'], 500);
    }

    /**
     * @OA\Post(
     *     path="/reset-password",
     *     summary="Reset user password",
     *     description="Reset the user's password using the provided token.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="token", type="string", example="reset-token"),
     *             @OA\Property(property="password", type="string", example="newpassword123"),
     *             @OA\Property(property="password_confirmation", type="string", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Password has been successfully reset.")
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
     *         response=400,
     *         description="Invalid or expired token.",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="The provided token is invalid or expired.")
     *         )
     *     )
     * )
     */
    public function resetPassword(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed', // Ensure the password is confirmed
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
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
            return response()->json(['message' => 'Password has been successfully reset.'], 200);
        }

        return response()->json(['error' => 'The provided token is invalid or expired.'], 400);
    }
}
