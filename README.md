# Backend – Cinescope 
## Descrizione

API REST per la gestione e la fornitura dei dati relativi a **film, attori e sale cinematografiche**.
Il backend è pensato per simulare un database reale con filtri, ordinamenti e endpoint dedicati.

## Obiettivi

* Fornire dati strutturati al frontend

* Supportare filtri dinamici tramite query params

* Simulare uno scenario backend realistico

* Separare la logica di accesso ai dati

## Stack utilizzato

* **PHP** - Linguaggio principale del backend.

* **API REST** - Architettura basata su endpoint RESTful.

* **PostgreSQL** - Database relazionale utilizzato per la persistenza dei dati.

* **Libreria Simple Rest API** - Libreria custom, ispirata a Laravel (https://github.com/codingspook/rest-api-php)

## Task principali

* Creazione delle collezioni:

    * Movies

    * Actors

    * Halls

* Endpoint per:

    * liste complete

    * dettaglio singola risorsa

* Supporto a:

    * filtri combinabili

    * ordinamento

    * limit dei risultati

* Endpoint dedicati per:

    * generi

    * registi

    * nazionalità

<<<<<<< HEAD
    * città

## Stato

Backend stabile e pronto per essere esteso con **operazioni CRUD complete**.
Attualmente ottimizzato per la consultazione dei dati.
=======
    * città
>>>>>>> 288bde4a2ab4c83d7c5577bcda28ac61ae3e75ba
