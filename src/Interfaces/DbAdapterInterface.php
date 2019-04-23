<?php
/**
 * Date: 9/30/17
 * Time: 6:15 AM
 */

namespace CarlibModeler\Interfaces;


interface DbAdapterInterface
{
    public function __construct($username, $passwd, $host);
    public function createDatabase($database);
    public function createParentTableWithPrimaryKey($db, $parentTable, $parentPrimaryKey, $parentDescription);
    public function createChildTableWithForeignKey($db, $childTable, $foreignKey, $parentTable, $parentPrimaryKey, $childTableDescription);
    public function runQuery($sql);
    public function getQuoteStyle();
    public function columnExists($db, $table, $column);
    public function primaryKeyExists($db, $table, $primaryKey);
    public function createDefaultTable($db, $table, $description);
}