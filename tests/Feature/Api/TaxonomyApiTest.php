<?php

use App\Models\Taxonomy;
use App\Models\Term;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can list taxonomies', function () {
    Taxonomy::factory()->count(3)->create();

    $response = $this->getJson(route('api.v1.taxonomies.index'));

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

it('can show a taxonomy with terms', function () {
    $taxonomy = Taxonomy::factory()->create();
    $term = Term::create([
        'taxonomy_id' => $taxonomy->id,
        'name' => 'Electronic',
        'slug' => 'electronic',
    ]);

    $response = $this->getJson(route('api.v1.taxonomies.show', $taxonomy));

    $response->assertOk()
        ->assertJson([
            'data' => [
                'id' => (string) $taxonomy->id,
                'attributes' => [
                    'name' => $taxonomy->name,
                    'slug' => $taxonomy->slug,
                ],
                'relationships' => [
                    'terms' => [
                        'data' => [
                            [
                                'id' => (string) $term->id,
                                'attributes' => [
                                    'name' => 'Electronic',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
});
