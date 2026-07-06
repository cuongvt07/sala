<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Nhân viên bị khóa toà: ép vùng dữ liệu về đúng toà của họ trên mọi request,
        // để không thể tự chuyển sang xem/sửa dữ liệu toà khác qua session.
        if ($user->isAreaRestricted()) {
            session(['admin_selected_area_id' => $user->area_id]);
        }

        // super_admin has all permissions
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        $routeName = $request->route()->getName();

        // Check if user has permission for the current route
        if ($routeName && !$user->hasPermission($routeName)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        return $next($request);
    }
}
