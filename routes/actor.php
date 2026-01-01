<?php

/* Routes per gestione attori */

use App\Database\DB;
use App\Utils\Response;
use App\Models\Actor;
use App\Utils\Request;
use Pecee\SimpleRouter\SimpleRouter as Router;

/**
 * GET /api/actors - Lista utenti
 */
Router::get('/actors', function () {
    try {
        $params = Actor::filterParams($_GET);

        //Verifico se ci sono delle query string
        $actors = $params !== null 
            ? Actor::filter($params) 
            : Actor::all();
        
        Response::success($actors)->send();
    } catch (\Exception $e) {
        Response::error("Errore nel recupero degli attori: " . $e->getMessage() . " " . $e->getFile() . " " . $e->getLine(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * GET /api/actors/nationalities - Lista di tutte le nazionalità
 */
Router::get('/actors/nationalities', function() {
    try {
        //Mi prendo tutte le nazionalità
        $nationalities = Actor::getAllNationality();

        Response::success($nationalities)->send();
    } catch(\Exception $e) {
        Response::error("Errore nel recupero delle nazionalità: " . $e->getMessage() . " " . $e->getFile() . " " . $e->getLine(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * GET /api/actors/{id} - Lista utenti
 */
Router::get('/actors/{id}', function ($id) {
    try {
        $actor = Actor::find($id);

        if($actor === null) {
            Response::error('Attore non trovato', Response::HTTP_NOT_FOUND)->send();
        }

        Response::success($actor)->send();
    } catch (\Exception $e) {
        Response::error("Errore nel recupero degli attori: " . $e->getMessage() . " " . $e->getFile() . " " . $e->getLine(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/* 
    * GET /api/actors/{id}/movies - Lista di film in cui ha recitato l'attore
*/

Router::get('/actors/{id}/movies', function ($id) {
    try {
        $actor = Actor::find($id);

        if($actor === null) {
            Response::error('Attore non trovato', Response::HTTP_NOT_FOUND)->send();
        }

        //Mi prendo i film dell'attore
        $movies = $actor->movies;

        Response::success($movies)->send();
    } catch (\Exception $e) {
        Response::error("Errore nel recupero degli attori: " . $e->getMessage() . " " . $e->getFile() . " " . $e->getLine(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * POST /api/actors - Crea nuovo Attore
 */
Router::post('/actors', function () {
    try {
        $request = new Request();
        $data = $request->json();

        // Validazione
        if(!isset($data['name'])) {
            Response::error('Nome è obbligatorio', Response::HTTP_BAD_REQUEST, array_map(fn($field) => "Il campo {$field} è obbligatorio", ['name']))->send();
            return;
        }

        $errors = Actor::validate($data);
        if (!empty($errors)) {
            Response::error('Errore di validazione', Response::HTTP_BAD_REQUEST, $errors)->send();
            return;
        }

        $actor = Actor::create($data);

        Response::success($actor, Response::HTTP_CREATED, "Attore creato con successo")->send();
    } catch (\Exception $e) {
        Response::error("Errore durante la creazione del nuovo attore: " . $e->getMessage() . " " . $e->getFile() . " " . $e->getLine(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

Router::match(['put', 'patch'], '/actors/{id}', function($id) {
    try {
        $request = new Request();
        $data = $request->json();

        $actor = Actor::find($id);
        if($actor === null) {
            Response::error('Attore non trovato', Response::HTTP_NOT_FOUND)->send();
        }

        $errors = Actor::validate(array_merge($data, ['id' => $id]));
        if (!empty($errors)) {
            Response::error('Errore di validazione', Response::HTTP_BAD_REQUEST, $errors)->send();
            return;
        }

        $actor->update($data);

        Response::success($actor, Response::HTTP_OK, "Attore aggiornato con successo")->send();
    } catch (\Exception $e) {
        Response::error("Errore durante l'aggiornamento dell'attore: " . $e->getMessage() . " " . $e->getFile() . " " . $e->getLine(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

Router::delete('/actors/{id}', function($id) {
    try {
        $actor = Actor::find($id);
        if($actor === null) {
            Response::error('Attore non trovato', Response::HTTP_NOT_FOUND)->send();
        }

        $actor->delete();

        Response::success(null, Response::HTTP_OK, "Attore eliminato con successo")->send();
    } catch (\Exception $e) {
        Response::error("Errore durante l'eliminazione dell'attore: " . $e->getMessage() . " " . $e->getFile() . " " . $e->getLine(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});