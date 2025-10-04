<?php
declare(strict_types=1);
// nutno ještě přidat ověření vstupních dat
// a ošetření chyb
namespace App\Presentation\ApiModule;

use Nette;
use Nette\Application\Responses\JsonResponse;
use Nette\Database\Explorer;

final class GeneratorPresenter extends Nette\Application\UI\Presenter
{
    private Explorer $db;

    public function __construct(Explorer $db)
    {
        parent::__construct();
        $this->db = $db;
    }

    //vytvoření generátoru
    public function actionCreate(): void
    {
        $data = $this->getHttpRequest()->getRawBody();
        $json = json_decode($data, true);

        if (!$json || !isset($json['name'])) {
            $this->sendResponse(new JsonResponse([
                'error' => 'Missing required field: name',
            ], 'application/json', 400));
        }
           if (!$json || !isset($json['max_output'])) {
            $this->sendResponse(new JsonResponse([
                'error' => 'Missing required field: max_output',
            ], 'application/json', 400));
        }

        $this->db->table('generator')->insert([
            'name' => $json['name'],
            'max_output' => $json['max_output'],
        ]);

        $this->sendResponse(new JsonResponse([
            'status' => 'success',
            'message' => 'Generator created',
        ]));
    }
    //smázání generátoru
    public function actionDelete(): void
{
    $data = $this->getHttpRequest()->getRawBody();
    $json = json_decode($data, true);

    if (!$json || !isset($json['id'])) {
        $this->sendResponse(new JsonResponse([
            'error' => 'Missing required field: id',
        ], 'application/json', 400));
    }

    $row = $this->db->table('generator')->get($json['id']);

    if (!$row) {
        $this->sendResponse(new JsonResponse([
            'error' => 'Generator not found',
        ], 'application/json', 404));
    }

    $row->delete();

    $this->sendResponse(new JsonResponse([
        'status' => 'success',
        'message' => 'Generator deleted',
    ]));
}

//update generátoru
public function actionUpdate(): void
{
    $data = $this->getHttpRequest()->getRawBody();
    $json = json_decode($data, true);

if (
    !$json
    || !isset($json['name'])
    || (isset($json['max_output']) && !is_numeric($json['max_output']))
    || (isset($json['last_load_percentage']) &&
        (!is_numeric($json['last_load_percentage']) ||
         $json['last_load_percentage'] < 0 ||
         $json['last_load_percentage'] > 100))
) {
    $this->sendResponse(new JsonResponse([
        'error' => 'Invalid input. Required: name (string), max_output (numeric), last_load_percentage (0–100)',
    ], 'application/json', 400));
}

    $row = $this->db->table('generator')->get($json['id']);

    if (!$row) {
        $this->sendResponse(new JsonResponse([
            'error' => 'Generator not found',
        ], 'application/json', 404));
    }

    $row->update([
        'name' => $json['name'] ?? $row->name,
        'max_output' => $json['max_output'] ?? $row->max_output,
        'last_load_percentage' => $json['last_load_percentage'] ?? $row->last_load_percentage,
    ]);

    $this->sendResponse(new JsonResponse([
        'status' => 'success',
        'message' => 'Generator updated',
    ]));
}
//získání všech generátorů
  public function actionList(): void
{
    $generators = $this->db->table('generator')->fetchAll();

    $data = [];

    foreach ($generators as $generator) {
        $data[] = [
            'id' => $generator->id,
            'name' => $generator->name,
            'max_output' => $generator->max_output,
            'on' => $generator->on,
            'last_load_percentage' => $generator->last_load_percentage,
        ];
    }

    $this->sendResponse(new JsonResponse($data));
}

//získání jednoho generátoru
public function actionGet(): void
{
    $data = $this->getHttpRequest()->getRawBody();
    $json = json_decode($data, true);

    if (!$json || !isset($json['id']) || !is_numeric($json['id'])) {
        $this->sendResponse(new JsonResponse([
            'error' => 'Missing or invalid generator id',
        ], 'application/json', 400));
    }

    $generator = $this->db->table('generator')->get($json['id']);

    if (!$generator) {
        $this->sendResponse(new JsonResponse([
            'error' => 'Generator not found',
        ], 'application/json', 404));
    }

    $this->sendResponse(new JsonResponse([
        'status' => 'success',
        'generator' => [
            'id' => $generator->id,
            'name' => $generator->name,
            'max_output' => $generator->max_output,
            'on' => $generator->on,
            'last_load_percentage' => $generator->last_load_percentage,
        ],
    ]));
}

}