<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureBranchSession
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && !session('current_branch_id')) {
            $user = auth()->user();
            $branch = null;

            if ($user->vendor_id) {
                $branch = $user->primaryBranch() ?? $user->branches()->first();
            }

            if ($branch) {
                session(['current_branch_id' => $branch->id, 'current_branch_name' => $branch->name]);
            }
        }

        return $next($request);
    }
}
