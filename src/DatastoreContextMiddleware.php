<?php

namespace Envor\Datastore;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DatastoreContextMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle(Request $request, Closure $next)
    {

        $datastoreContext = app(Contracts\HasDatastoreContext::class)->datastoreContext();

        if (! $datastoreContext) {
            return $next($request);
        }

        if (! cache()->has('datastore_migrated_'.$datastoreContext->id)) {
            $datastoreContext->migrate();
            cache()->put('datastore_migrated_'.$datastoreContext->id, true, now()->addDay());
        }

        $datastoreContext->configure()->use();

        if (config('app.debug')) {
            Log::debug('context configured', ['context' => config('database.default')]);
            session()->put('default_connection', config('database.default'));
            session()->put('connection_details', config('database.connections.'.config('database.default')));
        }

        return $next($request);
    }
}
