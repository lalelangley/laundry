<?

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MenuRole;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $menu, $action = 'view')
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // cek permission
        $permission = MenuRole::where('role_id', $user->role_id)
            ->whereHas('menu', function ($q) use ($menu) {
                $q->where('slug', $menu);
            })
            ->first();

        if (!$permission || $permission['can_' . $action] != 1) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
