<?php


declare(strict_types=1);

namespace App\Presentation\ApiModule;

use Nette;
use Nette\Application\Responses\JsonResponse;
use Nette\Database\Explorer;

final class SessionPresenter extends Nette\Application\UI\Presenter
{
    private Explorer $db;

    public function __construct(Explorer $db)
    {
        parent::__construct();
        $this->db = $db;
    }

    //vytvoření session a zapnutí generátoru---
    public function actionCreate(): void
    {
        $data = $this->getHttpRequest()->getRawBody();
        $json = json_decode($data, true);

        if (!$json || !isset($json['generator_id']) || !is_numeric($json['generator_id'])) {
            $this->sendResponse(new JsonResponse([
                'error' => 'Missing or invalid generator_id',
            ], 'application/json', 400));
        }

        $generator = $this->db->table('generator')->get($json['generator_id']);

        if (!$generator) {
            $this->sendResponse(new JsonResponse([
                'error' => 'Generator not found',
            ], 'application/json', 404));
        }
           if ($generator->on) {
        $this->sendResponse(new JsonResponse([
            'error' => 'Generator is already running',
        ], 'application/json', 400));
    }

        // Zapnout generátor
        $generator->update(['on' => true]);

        // Vytvořit session
        $this->db->table('session')->insert([
            'generator_id' => $generator->id,
            'start_datetime' => new \DateTime(),
        ]);

        $this->sendResponse(new JsonResponse([
            'status' => 'success',
            'message' => 'Session created and generator turned on',
        ]));
    }

//vypnutí generátoru a ukončení session---
    public function actionStop(): void
{
    $data = $this->getHttpRequest()->getRawBody();
    $json = json_decode($data, true);

    if (!$json || !isset($json['generator_id']) || !is_numeric($json['generator_id'])) {
        $this->sendResponse(new JsonResponse([
            'error' => 'Missing or invalid generator_id',
        ], 'application/json', 400));
    }

    $generator = $this->db->table('generator')->get($json['generator_id']);

    if (!$generator) {
        $this->sendResponse(new JsonResponse([
            'error' => 'Generator not found',
        ], 'application/json', 404));
    }

    //  Generátor už je vypnutý
    if (!$generator->on) {
        $this->sendResponse(new JsonResponse([
            'error' => 'Generator is already off',
        ], 'application/json', 400));
    }

    // Najít poslední session bez end_datetime
    $session = $this->db->table('session')
        ->where('generator_id', $generator->id)
        ->where('end_datetime IS NULL')
        ->order('start_datetime DESC')
        ->fetch();

    if (!$session) {
        $this->sendResponse(new JsonResponse([
            'error' => 'Running session not found for this generator',
        ], 'application/json', 404));
    }

    //  Vypnout generátor
    $generator->update(['on' => false]);

    //  Aktualizovat end_datetime u session
    $session->update([
        'end_datetime' => new \DateTime(),
    ]);

    $this->sendResponse(new JsonResponse([
        'status' => 'success',
        'message' => 'Generator turned off and session ended',
    ]));
}

//aktualizace zátěže a výkonu v běžící session---
public function actionUpdateLoad(): void
{
    $data = $this->getHttpRequest()->getRawBody();
    $json = json_decode($data, true);

    // Kontrola vstupních dat
    if (
        !$json ||
        !isset($json['generator_id']) ||
        !isset($json['load_percentage']) ||
        !is_numeric($json['generator_id']) ||
        !is_numeric($json['load_percentage']) ||
        $json['load_percentage'] < 0 ||
        $json['load_percentage'] > 100
    ) {
        $this->sendResponse(new JsonResponse([
            'error' => 'Invalid input: generator_id must be numeric and load_percentage must be 0–100',
        ], 'application/json', 400));
    }

    $generatorId = (int) $json['generator_id'];
    $load = (float) $json['load_percentage'];

    // Načti generátor
    $generator = $this->db->table('generator')->get($generatorId);
    if (!$generator) {
        $this->sendResponse(new JsonResponse([
            'error' => 'Generator not found',
        ], 'application/json', 404));
    }

    // Najdi běžící session pro daný generátor
    $session = $this->db->table('session')
        ->where('generator_id', $generatorId)
        ->where('end_datetime IS NULL')
        ->order('start_datetime DESC')
        ->fetch();

    if (!$session) {
        $this->sendResponse(new JsonResponse([
            'error' => 'No active session found for this generator',
        ], 'application/json', 404));
    }

    // Vypočti aktuální výkon
    $powerOutput = round($generator->max_output * ($load / 100));

    // Aktualizuj session
    $session->update([
        'load_percentage' => $load,
        'power_output' => $powerOutput,
    ]);

    $this->sendResponse(new JsonResponse([
        'status' => 'success',
        'message' => 'Session load updated',
        'power_output' => $powerOutput,
    ]));
}
//získání aktuální běžící session pro daný generátor---
public function actionCurrent(): void
{
    $data = $this->getHttpRequest()->getRawBody();
    $json = json_decode($data, true);

    if (!$json || !isset($json['generator_id']) || !is_numeric($json['generator_id'])) {
        $this->sendResponse(new JsonResponse([
            'error' => 'Missing or invalid generator_id',
        ], 'application/json', 400));
    }

    $session = $this->db->table('session')
        ->where('generator_id', $json['generator_id'])
        ->where('end_datetime IS NULL')
        ->order('start_datetime DESC')
        ->fetch();

    if (!$session) {
        $this->sendResponse(new JsonResponse([
            'error' => 'No active session found for this generator',
        ], 'application/json', 404));
    }

    $this->sendResponse(new JsonResponse([
        'status' => 'success',
        'session' => [
            'session_id' => $session->session_id,
            'generator_id' => $session->generator_id,
            'start_datetime' => $session->start_datetime,
            'power_output' => $session->power_output,
            'load_percentage' => $session->load_percentage,
            'end_datetime' => $session->end_datetime, // bude NULL
        ],
    ]));
}

}