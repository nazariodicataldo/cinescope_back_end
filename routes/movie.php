<?php

/* Routes per gestione film */


use App\Utils\Response;
use App\Models\Movie;
use App\Utils\Request;
use Pecee\SimpleRouter\SimpleRouter as Router;

/**
 * GET /api/movies - Lista film
 */
Router::get('/movies', function () {
    try {
        $params = Movie::filterParams($_GET);

        //Verifico se ci sono delle query string
        $movies = $params !== null 
            ? Movie::filter($params) 
            : Movie::all();

        Response::success($movies)->send();
    } catch (\Exception $e) {
        Response::error("Errore nel recupero dei film: " . $e->getMessage() . " " . $e->getFile() . " " . $e->getLine(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * GET /api/movies/{id} - Lista film
 */
Router::get('/movies/{id}', function ($id) {
    try {
        $movie = Movie::find($id);

        if($movie === null) {
            Response::error('Film non trovato', Response::HTTP_NOT_FOUND)->send();
        }

        Response::success($movie)->send();
    } catch (\Exception $e) {
        Response::error("Errore nel recupero dei film: " . $e->getMessage() . " " . $e->getFile() . " " . $e->getLine(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * GET /api/movies/{id}/actors - Casting di un film
 */
Router::get('/movies/{id}/actors', function ($id) {
    try {
        $movie = Movie::find($id);

        if($movie === null) {
            Response::error('Film non trovato', Response::HTTP_NOT_FOUND)->send();
        }

        $actors = $movie->actors;

        Response::success($actors)->send();
    } catch (\Exception $e) {
        Response::error("Errore nel recupero dei film: " . $e->getMessage() . " " . $e->getFile() . " " . $e->getLine(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * POST /api/movies - Crea nuovo Attore
 */
Router::post('/movies', function () {
    try {
        $request = new Request();
        $data = $request->json();

        // Validazione
        if(!isset($data['title']) || !isset($data['director']) ) {
            Response::error('Campi richiesti vuoti', Response::HTTP_BAD_REQUEST, array_map(fn($field) => "Il campo {$field} è obbligatorio", ['title', 'director']))->send();
            return;
        }

        $errors = Movie::validate($data);
        if (!empty($errors)) {
            Response::error('Errore di validazione', Response::HTTP_BAD_REQUEST, $errors)->send();
            return;
        }

        $movie = Movie::create($data);

        Response::success($movie, Response::HTTP_CREATED, "Film creato con successo")->send();
    } catch (\Exception $e) {
        Response::error("Errore durante la creazione del nuovo film: " . $e->getMessage() . " " . $e->getFile() . " " . $e->getLine(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

Router::match(['put', 'patch'], '/movies/{id}', function($id) {
    try {
        $request = new Request();
        $data = $request->json();

        $movie = Movie::find($id);
        if($movie === null) {
            Response::error('Film non trovato', Response::HTTP_NOT_FOUND)->send();
        }

        $errors = Movie::validate(array_merge($data, ['id' => $id]));
        if (!empty($errors)) {
            Response::error('Errore di validazione', Response::HTTP_BAD_REQUEST, $errors)->send();
            return;
        }

        $movie->update($data);

        Response::success($movie, Response::HTTP_OK, "Film aggiornato con successo")->send();
    } catch (\Exception $e) {
        Response::error("Errore durante l'aggiornamento del film: " . $e->getMessage() . " " . $e->getFile() . " " . $e->getLine(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

Router::delete('/movies/{id}', function($id) {
    try {
        $movie = Movie::find($id);
        if($movie === null) {
            Response::error('Film non trovato', Response::HTTP_NOT_FOUND)->send();
        }

        $movie->delete();

        Response::success(null, Response::HTTP_OK, "Film eliminato con successo")->send();
    } catch (\Exception $e) {
        Response::error("Errore durante l'eliminazione del film: " . $e->getMessage() . " " . $e->getFile() . " " . $e->getLine(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});