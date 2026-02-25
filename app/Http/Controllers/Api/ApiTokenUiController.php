<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiTokenUiController extends Controller
{
    /**
     * Display the API token management UI.
     */
    public function index()
    {
        $tokens = ApiToken::with('createdBy')
            ->orderByDesc('created_at')
            ->paginate(20);

        $users = User::all(['id', 'name', 'email']);

        return view('api.tokens.index', compact('tokens', 'users'));
    }

    /**
     * Create a new token from the UI.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'created_by_id' => 'nullable|exists:users,id',
            'expires_in_days' => 'nullable|integer|min:1|max:365',
            'permissions' => 'required|array',
            'permissions.*' => 'string|in:read,write,sync,ai,admin',
        ]);

        try {
            $tokenValue = ApiToken::generateToken();

            $token = ApiToken::create([
                'name' => $validated['name'],
                'token' => $tokenValue,
                'created_by_id' => $validated['created_by_id'] ?? auth()->id(),
                'expires_at' => isset($validated['expires_in_days'])
                    ? now()->addDays($validated['expires_in_days'])
                    : null,
                'permissions' => $validated['permissions'],
                'is_active' => true,
            ]);

            return redirect()
                ->route('api.tokens.ui')
                ->with('success', 'Token created successfully!')
                ->with('new_token', $tokenValue)
                ->with('token_id', $token->id);
        } catch (\Exception $e) {
            Log::error('Error creating API token: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Error creating token: ' . $e->getMessage());
        }
    }

    /**
     * Revoke a token.
     */
    public function revoke(ApiToken $apiToken)
    {
        try {
            $apiToken->update(['is_active' => false]);
            return redirect()
                ->route('api.tokens.ui')
                ->with('success', 'Token revoked successfully!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error revoking token: ' . $e->getMessage());
        }
    }

    /**
     * Delete a token permanently.
     */
    public function destroy(ApiToken $apiToken)
    {
        try {
            $apiToken->delete();
            return redirect()
                ->route('api.tokens.ui')
                ->with('success', 'Token deleted successfully!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error deleting token: ' . $e->getMessage());
        }
    }

    /**
     * Update token name or permissions.
     */
    public function update(Request $request, ApiToken $apiToken)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'required|array',
            'permissions.*' => 'string|in:read,write,sync,ai,admin',
        ]);

        try {
            $apiToken->update([
                'name' => $validated['name'],
                'permissions' => $validated['permissions'],
            ]);

            return redirect()
                ->route('api.tokens.ui')
                ->with('success', 'Token updated successfully!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error updating token: ' . $e->getMessage());
        }
    }
}
