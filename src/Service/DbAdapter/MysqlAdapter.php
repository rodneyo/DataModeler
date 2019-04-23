<?php
/**
 * Date: 9/30/17
 * Time: 6:38 AM
 */

namespace CarlibModeler\Service\DbAdapter;

use PDO;
use CarlibModeler\Interfaces\DbAdapterInterface;

class MysqlAdapter implements DbAdapterInterface
{

    protected $username;
    protected $passwd;
    protected $host;
    protected $dbConn;
    protected $dbQuote = '`';

    public function __construct($username, $passwd, $host)
    {
        $this->username = $username;
        $this->passwd = $passwd;
        $this->host = $host;

        $this->dbConn = new PDO('mysql:host=' . $this->host,
            $this->username, $this->passwd, [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ]);
    }

    /**
     * @param $database
     */
    public function createDatabase($database)
    {
        $sql = "create database if not exists {$this->dbQuote}{$database}{$this->dbQuote}";

        $this->runQuery($sql);
    }

    /**
     * @param $db
     * @param $parentTable
     * @param $parentPrimaryKey
     * @param $parentDescription
     */
    public function createParentTableWithPrimaryKey($db, $parentTable, $parentPrimaryKey, $parentDescription)
    {
        $sql =  "create table {$this->dbQuote}{$db}{$this->dbQuote}.{$this->dbQuote}{$parentTable}{$this->dbQuote}(
                   {$this->dbQuote}{$parentPrimaryKey}{$this->dbQuote} CHAR NOT NULL,
                  primary key({$this->dbQuote}{$parentPrimaryKey}{$this->dbQuote}))
                  engine  = InnoDB
                  comment = '{$parentDescription}';
              ";
        try {
            $this->runQuery($sql);
        } catch (\Exception $e) {

            if ($this->primaryKeyExists($db, $parentTable, $parentPrimaryKey)) {
                return;
            }

            if (! $this->columnExists($db, $parentTable, $parentPrimaryKey)) {
                $sql
                    = "alter table {$this->dbQuote}{$db}{$this->dbQuote}.{$this->dbQuote}{$parentTable}{$this->dbQuote} 
                      add column {$this->dbQuote}{$parentPrimaryKey}{$this->dbQuote} CHAR NOT NULL,
                      add unique key {$this->dbQuote}ukey_{$parentPrimaryKey}{$this->dbQuote}
                      ({$this->dbQuote}{$parentPrimaryKey}{$this->dbQuote}) 
                    ";
                $this->runQuery($sql);
            }
        }

    }

    /**
     * @param $db
     * @param $childTable
     * @param $foreignKey
     * @param $parentTable
     * @param $parentPrimaryKey
     * @param $childTableDescription
     */
    public function createChildTableWithForeignKey($db, $childTable, $foreignKey, $parentTable, $parentPrimaryKey, $childTableDescription)
    {
        $sql =   "create table {$this->dbQuote}{$db}{$this->dbQuote}.{$this->dbQuote}{$childTable}{$this->dbQuote} (
                  {$this->dbQuote}{$foreignKey}{$this->dbQuote} CHAR NOT NULL,
                  foreign key({$this->dbQuote}{$foreignKey}{$this->dbQuote}) 
                  references {$this->dbQuote}{$parentTable}{$this->dbQuote}({$this->dbQuote}{$parentPrimaryKey}{$this->dbQuote}))
                  engine  = InnoDB
                  comment = '{$childTableDescription}';
              ";

        try {
            $this->runQuery($sql);
        } catch (\Exception $e) {

            if ($this->columnExists($db, $childTable, $foreignKey)) {
                $sql =   "alter table {$this->dbQuote}{$db}{$this->dbQuote}.{$this->dbQuote}{$childTable}{$this->dbQuote}
                      add foreign key({$this->dbQuote}{$foreignKey}{$this->dbQuote}) 
                      references {$this->dbQuote}{$parentTable}{$this->dbQuote}({$this->dbQuote}{$parentPrimaryKey}{$this->dbQuote})
                    ";
                $this->runQuery($sql);
            } else {
                $sql =   "alter table {$this->dbQuote}{$db}{$this->dbQuote}.{$this->dbQuote}{$childTable}{$this->dbQuote}
                      add column {$this->dbQuote}{$foreignKey}{$this->dbQuote} CHAR NOT NULL,
                      add foreign key({$this->dbQuote}{$foreignKey}{$this->dbQuote})
                      references {$this->dbQuote}{$parentTable}{$this->dbQuote}({$this->dbQuote}{$parentPrimaryKey}{$this->dbQuote})
                    ";
                $this->runQuery($sql);
            }
        }
    }

    /**
     * @param $db
     * @param $table
     * @param $description
     */
    public function createDefaultTable($db, $table, $description)
    {
        $sql =  "create table if not exists {$this->dbQuote}{$db}{$this->dbQuote}.{$this->dbQuote}{$table}{$this->dbQuote}(
                   {$this->dbQuote}default{$this->dbQuote} CHAR NOT NULL)
                  engine  = InnoDB
                  comment = '{$description}';
              ";

        $this->runQuery($sql);
    }

    /**
     * @param $db
     * @param $table
     * @param $column
     *
     * @return bool
     */
    public function columnExists($db, $table, $column)
    {
        $sql = "
            select column_name from information_schema.columns
            where table_schema = '{$db}'
            and table_name = '{$table}'
            and column_name = '{$column}'
        ";

        $statement = $this->dbConn->query($sql);
        $results = $statement->fetch();

        if ($results) {
            return true;
        }
        return false;

    }

    /**
     * @param $db
     * @param $table
     * @param $primaryKey
     *
     * @return bool
     */
    public function primaryKeyExists($db, $table, $primaryKey)
    {
        $sql = "select column_name
            from `information_schema`.`columns`
            where (table_schema = '{$db}')
            and (table_name = '{$table}')
            and (column_key = 'PRI');
        ";

        $statement = $this->dbConn->query($sql);
        $results = $statement->fetch();

        if ($results['column_name'] == $primaryKey) {
            return true;
        }
        return false;
    }

    /**
     * @param $sql
     *
     * @throws \Exception
     */
    public function runQuery($sql)
    {
        try {
            $this->dbConn->query($sql);
        } catch (\PDOException $e) {

            print($e->getMessage());
            print(PHP_EOL . PHP_EOL);
            throw new \Exception($e->errorInfo[1]);
        }
    }

    /**
     * @return string
     */
    public function getQuoteStyle()
    {
        return $this->dbQuote;
    }
}