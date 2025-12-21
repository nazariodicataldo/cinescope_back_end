<?php

/* Routes per gestione proiezioni */


use App\Utils\Response;
use App\Models\Projection;
use App\Utils\Request;
use Pecee\SimpleRouter\SimpleRouter as Router;

/**
 * GET /api/projections - Lista proiezioni
 */
Router::get('/projections', function () {
    try {
        $params = Projection::filterParams($_GET);

        $projections = $params !== null 
            ? Projection::filter($params) 
            : Projection::all();

        Response::success($projections)->send();
    } catch (\Exception $e) {
        Response::error("Errore nel recupero delle proiezioni: " . $e->getMessage() . " " . $e->getFile() . " " . $e->getLine(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * GET /api/projections/{id} - Lista proiezioni
 */
Router::get('/projections/{id}', function ($id) {
    try {
        $projection = Projection::find($id);

        if($projection === null) {
            Response::error('Proiezione non trovata', Response::HTTP_NOT_FOUND)->send();
        }

        Response::success($projection)->send();
    } catch (\Exception $e) {
        Response::error("Errore nel recupero delle proiezioni: " . $e->getMessage() . " " . $e->getFile() . " " . $e->getLine(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});


/**
 * POST /api/projections - Crea nuovo proiezione
 */
Router::post('/projections', function () {
    try {
        $request = new Request();
        $data = $request->json();

        // Validazione
        if(!isset($data['movie_id']) || !isset($data['hall_id']) ) {
            Response::error('Campi richiesti vuoti', Response::HTTP_BAD_REQUEST, array_map(fn($field) => "Il campo {$field} è obbligatorio", ['movie_id', 'hall_id']))->send();
            return;
        }

        $errors = Projection::validate($data);
        if (!empty($errors)) {
            Response::error('Errore di validazione', Response::HTTP_BAD_REQUEST, $errors)->send();
            return;
        }

        $projection = Projection::create($data);

        Response::success($projection, Response::HTTP_CREATED, "Proiezione creato con successo")->send();
    } catch (\Exception $e) {
        Response::error("Errore durante la creazione della nuova proiezione: " . $e->getMessage() . " " . $e->getFile() . " " . $e->getLine(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

Router::match(['put', 'patch'], '/projections/{id}', function($id) {
    try {
        $request = new Request();
        $data = $request->json();

        $projection = Projection::find($id);
        if($projection === null) {
            Response::error('Proiezione non trovata', Response::HTTP_NOT_FOUND)->send();
        }

        $errors = Projection::validate(array_merge($data, ['id' => $id]));
        if (!empty($errors)) {
            Response::error('Errore di validazione', Response::HTTP_BAD_REQUEST, $errors)->send();
            return;
        }

        $projection->update($data);

        Response::success($projection, Response::HTTP_OK, "Proiezione aggiornata con successo")->send();
    } catch (\Exception $e) {
        Response::error("Errore durante l'aggiornamento della proiezione: " . $e->getMessage() . " " . $e->getFile() . " " . $e->getLine(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

Router::delete('/projections/{id}', function($id) {
    try {
        $projection = Projection::find($id);
        if($projection === null) {
            Response::error('Proiezione non trovata', Response::HTTP_NOT_FOUND)->send();
        }

        $projection->delete();

        Response::success(null, Response::HTTP_OK, "Proiezione eliminata con successo")->send();
    } catch (\Exception $e) {
        Response::error("Errore durante l'eliminazione della proiezione: " . $e->getMessage() . " " . $e->getFile() . " " . $e->getLine(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});