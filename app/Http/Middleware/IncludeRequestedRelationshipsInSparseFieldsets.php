<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IncludeRequestedRelationshipsInSparseFieldsets
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->isMethod('GET') || !$request->is('api/v1/videos')) {
            return $next($request);
        }

        $include = $request->query('include');
        if (!is_string($include)) {
            return $next($request);
        }

        $includesVideoRatings = collect(explode(',', $include))
            ->map(fn (string $path) => explode('.', trim($path), 2)[0])
            ->contains('video-ratings');

        $fields = $request->query('fields');
        if (!$includesVideoRatings || !is_array($fields) || !isset($fields['videos'])) {
            return $next($request);
        }

        if (!is_string($fields['videos'])) {
            return $next($request);
        }

        $videoFields = array_filter(array_map('trim', explode(',', $fields['videos'])));
        if (!in_array('video-ratings', $videoFields, true)) {
            $videoFields[] = 'video-ratings';
            $fields['videos'] = implode(',', $videoFields);
            $request->query->set('fields', $fields);
        }

        return $next($request);
    }
}
