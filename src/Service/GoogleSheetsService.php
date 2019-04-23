<?php
/**
 * Date: 9/30/17
 * Time: 8:08 PM
 */

namespace CarlibModeler\Service;

use CarlibModeler\Interfaces\SpreadSheetServiceInterface;
use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\Exception\Exception;
use Google\Spreadsheet\Exception\SpreadsheetNotFoundException;
use Google\Spreadsheet\ServiceRequestFactory;
use Google\Spreadsheet\SpreadsheetService;
use Google_Client;
use Google_Service_Sheets;
use GuzzleHttp\Client;


class GoogleSheetsService implements SpreadSheetServiceInterface
{
    protected $service;
    protected $driveService;
    protected $client;
    protected $guzzleClient;
    protected $sheetId;
    protected $serviceSheet;
    protected $sheetName;
    protected $workSheetName;

    public function __construct($credentials)
    {
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentials);

        $this->client = new Google_Client();
        $guzzleClient = new Client(['verify' => false]);


        $this->client->useApplicationDefaultCredentials();
        $this->client->setHttpClient($guzzleClient);
        $this->client->setScopes([
            'https://www.googleapis.com/auth/spreadsheets',
            'https://www.googleapis.com/auth/drive',
            'https://spreadsheets.google.com/feeds'
        ]);

        $this->service = new Google_Service_Sheets($this->client);

        $accessToken = $this->client->fetchAccessTokenWithAssertion()["access_token"];
        ServiceRequestFactory::setInstance(
            new DefaultServiceRequest($accessToken)
        );
    }

    public function getData()
    {
        try {
            $spreadsheet = (new SpreadsheetService())->getSpreadsheetFeed()
                ->getByTitle($this->sheetName);

            $worksheets = $spreadsheet->getWorksheetFeed();
            $worksheet = $worksheets->getByTitle($this->workSheetName);
            $csv = explode(PHP_EOL, $worksheet->getCsv());

            return array_slice($csv, 3);

        } catch (SpreadsheetNotFoundException $e) {
            echo "Spreadsheet not found" . PHP_EOL;
            echo $e->getTraceAsString();

        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }

    public function setSheetId($sheetId)
    {
        $this->sheetId = $sheetId;
    }

    public function getSheetId()
    {
        return $this->sheetId;
    }

    /**
     * @return mixed
     */
    public function getWorkSheetName()
    {
        return $this->sheetName;
    }

    /**
     * @return mixed
     */
    public function getSheetName()
    {
        return $this->sheetName;
    }

    /**
     * @param $sheetName
     *
     * @return mixed
     */
    public function setSheetName($sheetName)
    {
        $this->sheetName = $sheetName;
    }

    /**
     * @param $workSheetName
     *
     * @return mixed
     */
    public function setWorkSheetName($workSheetName)
    {
        $this->workSheetName = $workSheetName;
    }
}