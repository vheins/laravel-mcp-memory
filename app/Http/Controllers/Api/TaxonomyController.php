<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaxonomyResource;
use App\Models\Taxonomy;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaxonomyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $taxonomies = Taxonomy::with('terms')->latest()->paginate();

        return TaxonomyResource::collection($taxonomies);
    }

    /**
     * Display the specified resource.
     */
    public function show(Taxonomy $taxonomy): TaxonomyResource
    {
        $taxonomy->load('terms');

        return new TaxonomyResource($taxonomy);
    }
}
