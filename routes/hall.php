<?php

/* Routes per gestione sale */


use App\Utils\Response;
use App\Models\Hall;
use App\Utils\Request;
use Pecee\SimpleRouter\SimpleRouter as Router;

/**
 * GET /api/halls - Lista sale
 */
Router::get('/halls', function () {
    try {
        $params = Hall::filterParams($_GET);

        //Verifico se ci sono delle query string
        $halls = $params !== null 
            ? Hall::filter($params) 
            : Hall::all();

        Response::success($halls)->send();
    } catch (\Exception $e) {
        Response::error("Errore nel recupero delle sale: " . $e->getMessage() . " " . $e->getFile() . " " . $e->getLine(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * GET /api/halls/cities - Lista di tutte le città
 */
Router::get('/halls/cities', function() {
    try {
        //Mi prendo tutte le nazionalità
        $cities = Hall::getAllCities();

        Response::success($cities)->send();
    } catch(\Exception $e) {
        Response::error("Errore nel recupero delle nazionalità: " . $e->getMessage() . " " . $e->getFile() . " " . $e->getLine(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * GET /api/halls/min_place - numero di posti più basso
 */
Router::get('/halls/min_place', function() {
    try {
        //Mi prendo tutte le nazionalità
        $min = Hall::getMinPlace();

        if(empty($min)) {
            Response::error("Nessun numero di posti minimo trovato", Response::HTTP_BAD_REQUEST)->send();
        }

        Response::success($min[0])->send();
    } catch(\Exception $e) {
        Response::error("Errore nel recupero del numero di posti più basso: " . $e->getMessage() . " " . $e->getFile() . " " . $e->getLine(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * GET /api/halls/max_place - numero di posti più alto
 */
Router::get('/halls/max_place', function() {
    try {
        //Mi prendo tutte le nazionalità
        $max = Hall::getMaxPlace();

        if(empty($max)) {
            Response::error("Nessun numero di posti massimo trovato", Response::HTTP_BAD_REQUEST)->send();
        }

        Response::success($max[0])->send();
    } catch(\Exception $e) {
        Response::error("Errore nel recupero del numero di posti più alto: " . $e->getMessage() . " " . $e->getFile() . " " . $e->getLine(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * GET /api/halls/{id} - Lista sale
 */
Router::get('/halls/{id}', function ($id) {
    try {
        $hall = Hall::find($id);

        if($hall === null) {
            Response::error('Sala non trovata', Response::HTTP_NOT_FOUND)->send();
        }

        Response::success($hall)->send();
    } catch (\Exception $e) {
        Response::error("Errore nel recupero delle sale: " . $e->getMessage() . " " . $e->getFile() . " " . $e->getLine(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * GET /api/halls/{id}/projections - Lista proiezioni di una sala
 */
Router::get('/halls/{id}/projections', function ($id) {
    try {
        $hall = Hall::find($id);

        if($hall === null) {
            Response::error('Sala non trovata', Response::HTTP_NOT_FOUND)->send();
        }

        //Mi prendo tutte le proiezioni di una sala
        $projections = $hall->projections;

        //Per ogni proiezioni, mi prendo il film collegato
        $projections = array_map(function ($proj) {
            return [...(array)$proj, "movie" => $proj->movie];
        }, $projections);

        Response::success($projections)->send();
    } catch (\Exception $e) {
        Response::error("Errore nel recupero delle sale: " . $e->getMessage() . " " . $e->getFile() . " " . $e->getLine(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * POST /api/halls - Crea nuovo sala
 */
Router::post('/halls', function () {
    try {
        $request = new Request();
        $data = $request->json();

        // Validazione
        if(!isset($data['city']) || !isset($data['name']) ) {
            Response::error('Campi richiesti vuoti', Response::HTTP_BAD_REQUEST, array_map(fn($field) => "Il campo {$field} è obbligatorio", ['name', 'city']))->send();
            return;
        }

        $errors = Hall::validate($data);
        if (!empty($errors)) {
            Response::error('Errore di validazione', Response::HTTP_BAD_REQUEST, $errors)->send();
            return;
        }

        $hall = Hall::create($data);

        Response::success($hall, Response::HTTP_CREATED, "Sala creato con successo")->send();
    } catch (\Exception $e) {
        Response::error("Errore durante la creazione della nuova sala: " . $e->getMessage() . " " . $e->getFile() . " " . $e->getLine(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

Router::match(['put', 'patch'], '/halls/{id}', function($id) {
    try {
        $request = new Request();
        $data = $request->json();

        $hall = Hall::find($id);
        if($hall === null) {
            Response::error('Sala non trovata', Response::HTTP_NOT_FOUND)->send();
        }

        $errors = Hall::validate(array_merge($data, ['id' => $id]));
        if (!empty($errors)) {
            Response::error('Errore di validazione', Response::HTTP_BAD_REQUEST, $errors)->send();
            return;
        }

        $hall->update($data);

        Response::success($hall, Response::HTTP_OK, "Sala aggiornata con successo")->send();
    } catch (\Exception $e) {
        Response::error("Errore durante l'aggiornamento della sala: " . $e->getMessage() . " " . $e->getFile() . " " . $e->getLine(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

Router::delete('/halls/{id}', function($id) {
    try {
        $hall = Hall::find($id);
        if($hall === null) {
            Response::error('Sala non trovato', Response::HTTP_NOT_FOUND)->send();
        }

        $hall->delete();

        Response::success(null, Response::HTTP_OK, "Sala eliminata con successo")->send();
    } catch (\Exception $e) {
        Response::error("Errore durante l'eliminazione della sala: " . $e->getMessage() . " " . $e->getFile() . " " . $e->getLine(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});