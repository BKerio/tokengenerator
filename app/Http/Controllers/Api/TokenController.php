<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Meter;
use App\Models\TokenTransaction;
use App\Services\PrismTokenService;
use Illuminate\Support\Facades\Log;

class TokenController extends Controller
{
    private $prismService;

    public function __construct(PrismTokenService $prismService)
    {
        $this->prismService = $prismService;
    }

    /**
     * Generate a new Token for a Meter
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'meter_id' => 'required|string|exists:meters,_id',
            'amount' => 'required|numeric|min:25',
        ]);

        $meter = Meter::findOrFail($validated['meter_id']);
        $user = $request->user();

        // Load the vendor relationship to verify ownership against the User ID
        $meter->load('vendor');

        // 1. Authorization check
        $isOwner = $meter->vendor && ((string) $meter->vendor->user_id === (string) $user->id);
        $isAdmin = in_array($user->role, ['admin', 'system_admin']);

        if (!$isOwner && !$isAdmin) {
            Log::warning('Unauthorized vending attempt', [
                'meter_id' => $meter->id,
                'meter_vendor_user_id' => $meter->vendor->user_id ?? null,
                'requesting_user_id' => $user->id
            ]);
            return response()->json(['message' => 'Unauthorized to vend for this meter.'], 403);
        }

        try {
            // 2. Wrap in try-catch to connect to Prism and Generate
            $generatedTokens = $this->prismService->issueCreditToken($meter, $validated['amount']);
            
            // Extract token digits
            $tokenStrings = [];
            foreach ($generatedTokens as $token) {
                if (isset($token->tokenDec)) {
                    $tokenStrings[] = $token->tokenDec;
                } elseif (isset($token->tokenHex)) {
                    $tokenStrings[] = $token->tokenHex;
                }
            }

            // 3. Save to database history
            $transaction = TokenTransaction::create([
                'meter_id' => $meter->id,
                'vendor_id' => $user->id,
                'customer_id' => $meter->customers()->first()->id ?? null, // Link to first customer attached, if any
                'amount' => $validated['amount'],
                'tokens' => $tokenStrings,
                'status' => 'success',
                'description' => 'Successfully generated ' . count($tokenStrings) . ' token(s).'
            ]);

            return response()->json([
                'message' => 'Token generated successfully',
                'transaction' => $transaction,
                'tokens' => $tokenStrings
            ], 201);

        } catch (\Prism\PrismToken1\ApiException $e) {
            // Log explicitly formatted Prism error
            Log::error("Prism API Exception: {$e->eCode} - {$e->eMsgEn}");
            
            TokenTransaction::create([
                'meter_id' => $meter->id,
                'vendor_id' => $user->id,
                'amount' => $validated['amount'],
                'status' => 'failed',
                'description' => "Prism Error: {$e->eCode} - {$e->eMsgEn}"
            ]);

            return response()->json([
                'message' => 'Failed to generate token from Prism network',
                'error_code' => $e->eCode,
                'error_details' => $e->eMsgEn
            ], 502); // 502 Bad Gateway (upstream failed)

        } catch (\Exception $e) {
            Log::error("Prism Connection/System Error: " . $e->getMessage());

            TokenTransaction::create([
                'meter_id' => $meter->id,
                'vendor_id' => $user->id,
                'amount' => $validated['amount'],
                'status' => 'failed',
                'description' => 'System Error: ' . $e->getMessage()
            ]);

            return response()->json([
                'message' => 'An internal error occurred while connecting to the token server',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
