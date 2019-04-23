<?php
/**
 * Date: 9/29/17
 * Time: 8:51 AM
 */

require_once (__DIR__ . '/../vendor/autoload.php');

use Zend\Config\Config;
use CarlibModeler\Service\DbAdapter\MysqlAdapter;
use CarlibModeler\Service\GoogleSheetsService;

ini_set("auto_detect_line_endings", true);
$startRow = 0;
$modelData = [];

/**
 * SetUp
 */
$config = new Config(include __DIR__ . '/../config/local.php', true);
$spreadSheetService = new GoogleSheetsService($config->app['gSrvcCreds']);
$dbAdapter = new MysqlAdapter(
    $config->app['dbUser'], $config->app['dbPasswd'], $config->app['dbHost']);

$spreadSheetService->setSheetId($config->app['sheetId']);
$spreadSheetService->setSheetName($config->app['sheetName']);
$spreadSheetService->setWorkSheetName($config->app['workSheetName']);
$sheetData = $spreadSheetService->getData();
$selectDatabases = $config->get('app')->get('select_databases')->toArray();
/**
while ($sheetData->valid()) {
    $dataRecord = $spreadSheetService->getRecord($sheetData);
}
 **/
foreach ($sheetData as $sheetRow) {
    $dataRow = str_getcsv(trim($sheetRow));

    $status = trim($dataRow[0]);
    $parentTable = trim(str_replace('-', '_',
        str_replace('.', '_', $dataRow[3])));

    $parentPrimaryKey = (! empty(str_replace('-', '_',
        trim($dataRow[4]))) ? trim($dataRow[4]) : null);

    $parentDescription = trim($dataRow[3]);
    $childTable = (! empty(str_replace('-', '_',
        trim(str_replace('.', '_',
            $dataRow[7])))) ? trim(str_replace('.', '_', $dataRow[7])) : null);

    $foreignKey = (! empty(str_replace('-', '_',
        trim($dataRow[8]))) ? trim($dataRow[8]) : null);

    $cardinality = trim($dataRow[10]);
    $childTableDescription = $dataRow[7];
    $database = [
        'core' => trim($dataRow[11]),
        'accounting' => trim($dataRow[12]),
        'trust' => trim($dataRow[13]),
        'commission_payroll' => trim($dataRow[14]),
        'sales_marketing' => trim($dataRow[15]),
        'ops_admin' => trim($dataRow[16]),
        'credit_collections' => trim($dataRow[17]),
        'acquisitions' => trim($dataRow[18])
    ];

    /**
     * @todo Refactor to the ModelBuilder class
     */

    $activeDatabases = array_keys(array_filter($database));

    // carry on if the model does not belong to any business function
    if (count($activeDatabases) <= 0) {
        continue;
    }

    foreach ($activeDatabases as $db) {
        // Determine which tables to generate
        if (! in_array($db, $selectDatabases)) {
            continue;
        }

        try {
            $dbAdapter->createDatabase($db);


            if (! preg_match('/#N\/A/i', $parentTable) ) {
                if (isset($parentTable) && isset($parentPrimaryKey)) {
                    $dbAdapter->createParentTableWithPrimaryKey($db,
                        $parentTable,
                        $parentPrimaryKey, $parentDescription
                    );
                } elseif (isset($parentTable)) {
                    $dbAdapter->createDefaultTable($db, $parentTable,
                        $parentDescription);
                }
            }

            if (! preg_match('/#N\/A/i', $childTable) ) {
                if (isset($childTable) && isset($foreignKey)) {
                    $dbAdapter->createChildTableWithForeignKey($db, $childTable,
                        $foreignKey, $parentTable, $parentPrimaryKey,
                        $childTableDescription
                    );
                } elseif (isset($childTable)) {
                    $dbAdapter->createDefaultTable($db, $childTable,
                        $childTableDescription);
                }
            }

        } catch (Exception $e) {
            print($e->getMessage() . PHP_EOL);
            print(PHP_EOL);
        }

    }
}

