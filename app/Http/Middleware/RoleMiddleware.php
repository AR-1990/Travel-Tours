<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $roles Comma-separated role IDs (e.g., "1,2,3")
     * @return mixed
     */
    public function handle($request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('admin.login');
        }

        $user = Auth::user();
        
        // Refresh user from database to ensure we have latest role_id
        $user->refresh();
        
        // Ensure role_id is loaded and valid
        if (!$user->role_id || $user->role_id === null) {
            Auth::logout();
            return redirect()->route('admin.login')->withErrors(['email' => 'Your account does not have a valid role assigned.']);
        }
        
        $userRoleId = (int) $user->role_id;
        
        // Handle middleware parameters
        // Support both comma-separated (1,2,3) and pipe-separated (1|2|3) formats
        $allowedRoleIds = [];
        if (count($roles) > 0) {
            $firstParam = (string) $roles[0];
            
            // Check for pipe separator first (1|2|3)
            if (strpos($firstParam, '|') !== false) {
                $allowedRoleIds = array_map('intval', array_filter(explode('|', $firstParam)));
            }
            // Check for comma separator (1,2,3)
            elseif (strpos($firstParam, ',') !== false) {
                $allowedRoleIds = array_map('intval', array_filter(explode(',', $firstParam)));
            }
            // Multiple separate parameters (when Laravel passes them separately)
            elseif (count($roles) > 1) {
                $allowedRoleIds = array_map('intval', array_filter($roles));
            }
            // Single parameter
            else {
                $allowedRoleIds = [(int) $firstParam];
            }
        }
        
        // Dashboard and admin routes are accessible to Admin (1), Manager (2), and Editor (3)
        if (in_array($userRoleId, $allowedRoleIds, true)) {
            return $next($request);
        }

        // Role 4 (Member) users should be redirected to their user area, not shown a 403
        if ($userRoleId == 4) {
            return redirect()->route('news-feed')->withErrors([
                'message' => 'Access denied. This area is only accessible to Admin, Manager, and Editor roles.'
            ]);
        }

        abort(403, 'Unauthorized action. Your role (ID: ' . $userRoleId . ') does not have access to this resource. Required roles: ' . implode(', ', $allowedRoleIds));
    }
}
