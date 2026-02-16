<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DesktopController extends Controller
{
    /**
     * Get desktop icons based on user role
     * Returns array of app identifiers that the user can access
     */
    public function icons(Request $request)
    {
        $user = $request->user();
        
        // If email not verified, only show verification app
        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'apps' => ['profile-verification']
            ]);
        }

        $role = $user->role;
        
        // Refined role-based access matrix
        $apps = match($role) {
            'Member' => [
                'nexus', 'terminal', 'elections', 'learning', 'finance', 'hall-of-fame', 'code-playground', 'transparency',
            ],
            'Treasurer' => [
                'nexus', 'terminal', 'elections', 'learning', 'hall-of-fame', 'code-playground', 'finance', 'transparency',
            ],
            'Secretary' => [
                'nexus', 'terminal', 'elections', 'learning', 'finance', 'hall-of-fame', 'code-playground',
            ],
            'Vice-President' => [
                'nexus', 'terminal', 'elections', 'learning', 'finance', 'hall-of-fame', 'code-playground', 'transparency',
            ],
            'President' => [
                'nexus', 'terminal', 'elections', 'learning', 'finance', 'hall-of-fame', 'code-playground', 'governance', 'transparency',
            ],
            'SuperAdmin' => [
                'nexus', 'terminal', 'elections', 'learning', 'finance', 'hall-of-fame', 'code-playground', 'governance', 'transparency', 'system-logs'
            ],
            default => ['nexus', 'terminal', 'learning', 'code-playground'] // Fallback
        };

        return response()->json([
            'apps' => $apps,
            'user' => [
                'name' => $user->name,
                'role' => $user->role,
                'email_verified' => $user->hasVerifiedEmail(),
                'permissions' => $this->getRolePermissions($user->role)
            ]
        ]);
    }

    /**
     * Get granular permissions for each role
     * This defines what actions a user can perform within each app
     */
    private function getRolePermissions($role)
    {
        return match($role) {
            'Member' => [
                'elections' => ['vote'],
                'events' => ['view'],
                'learning' => ['view', 'submit'],
                'finance' => ['view'],
                'hall-of-fame' => ['view'],
                'code-playground' => ['execute']
            ],
            'Treasurer' => [
                'elections' => ['vote'],
                'events' => ['view'],
                'learning' => ['view', 'submit'],
                'hall-of-fame' => ['view'],
                'code-playground' => ['execute'],
                'finance' => ['view', 'create', 'edit', 'delete'],
                'transparency' => ['view', 'create', 'edit']
            ],
            'Secretary' => [
                'elections' => ['vote'],
                'learning' => ['view', 'submit'],
                'finance' => ['view'],
                'hall-of-fame' => ['view'],
                'code-playground' => ['execute'],
                'events' => ['view', 'create', 'edit', 'delete'],
                'news' => ['view', 'create', 'edit', 'delete']
            ],
            'Vice-President' => [
                'elections' => ['view_results'],
                'events' => ['view'],
                'learning' => ['view', 'submit'],
                'finance' => ['view'],
                'hall-of-fame' => ['view'],
                'code-playground' => ['execute'],
                'news' => ['view']
            ],
            'President' => [
                'elections' => ['create', 'manage', 'view_results'], // Cannot vote!
                'events' => ['view'],
                'learning' => ['view', 'submit'],
                'finance' => ['view'], // View only, cannot edit
                'hall-of-fame' => ['view'],
                'code-playground' => ['execute'],
                'news' => ['view'],
                'governance' => ['view', 'manage']
            ],
            'SuperAdmin' => ['*'], // All permissions
            default => []
        };
    }
}
