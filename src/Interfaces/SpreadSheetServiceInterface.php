<?php
/**
 * Date: 10/1/17
 * Time: 8:35 AM
 */

namespace CarlibModeler\Interfaces;


interface SpreadSheetServiceInterface
{
    /**
     * SpreadSheetServiceInterface constructor.
     *
     * @param $setUp
     */
    public function __construct($setUp);

    /**
     * @return mixed
     */
    public function getData();

    /**
     * @return mixed
     */
    public function getSheetId();

    /**
     * @return mixed
     */
    public function getWorkSheetName();

    /**
     * @return mixed
     */
    public function getSheetName();

    /**
     * @param $sheetId
     *
     * @return mixed
     */
    public function setSheetId($sheetId);

    /**
     * @param $sheetName
     *
     * @return mixed
     */
    public function setSheetName($sheetName);

    /**
     * @param $workSheetName
     *
     * @return mixed
     */
    public function setWorkSheetName($workSheetName);
}