<?php
/**
 * Script para criar revistas mockadas no OJS via bootstrap direto.
 * Uso: php docker/seed-journals.php
 */

define('INDEX_FILE_LOCATION', dirname(__DIR__) . '/index.php');
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['HTTP_HOST']   = 'localhost';
$_SERVER['SERVER_PORT'] = '80';
$_SERVER['REQUEST_URI'] = '/index.php/index';

chdir(dirname(__DIR__));
require('lib/pkp/includes/bootstrap.php');

$journals = [
    ['name' => 'Revista Brasileira de Ciências',      'path' => 'rbciencias'],
    ['name' => 'Revista de Engenharia e Tecnologia',  'path' => 'rengtech'],
    ['name' => 'Revista de Saúde Pública',            'path' => 'rsaude'],
    ['name' => 'Revista de Educação e Pesquisa',      'path' => 'redu'],
];

$contextService = \APP\core\Services::get('context');

foreach ($journals as $data) {
    $journal = \APP\journal\Journal::newData();
    $journal->setData('name', $data['name'], 'pt_BR');
    $journal->setData('urlPath', $data['path']);
    $journal->setData('primaryLocale', 'pt_BR');
    $journal->setData('supportedLocales', ['pt_BR']);
    $journal->setData('supportedSubmissionLocales', ['pt_BR']);
    $journal->setData('enabled', 1);

    try {
        $id = $contextService->add($journal, new \PKP\core\PKPRequest());
        echo "✓ Criada: {$data['name']} (id=$id, path={$data['path']})\n";
    } catch (\Exception $e) {
        echo "✗ Erro em {$data['name']}: " . $e->getMessage() . "\n";
    }
}
