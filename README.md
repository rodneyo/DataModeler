Data Modeler
----

A CLI tool that uses the google sheets api to connect to
Documentation Tracker sheet.  It reads the DB Modeling
Entries tab as a CSV and parses the following columns.  

| Col Nbr | Variable Name |
|:---------|:---------------|
| 0       | status        |
| 3       | parentTable |
| 4 | parentPrimaryKey |
| 3 | parentDescription |
| 7 | childTable |
| 8 | foreignKey |
| 10| cardinality |
| 7 | childTableDescription |
| 12, 13, 14, 15, 16, 17, 18, 19 | Database |


The parsed data is used to create a series of database create statements that represent a data model
relationship.  The create statements are written to a standard txt file and then imported into MySQL.

At this point an EERD can be generated or other documentation.  

[SchemaSpy](http://schemaspy.org/) is used to generate an EERD and other related documentation.  SchemaSpy is
a standalone Java application used to reverse engineer and generate html based documentation

SchemaSpy Installation (assumes a ubuntu linux server)
---
> Project Website [SchemaSpy](http://schemaspy.org/)

> Download: [Jar File](https://github.com/schemaspy/schemaspy/releases/download/v6.0.0-rc2/schemaspy-6.0.0-rc2.jar)

> Helpful Article: [Use Schema Spy to Document your Database](https://medium.com/@gustavo.ponce.ch/how-to-use-schemaspy-to-document-your-database-4046fdecfe83)

> Dependencies on ubuntu 16.04
* java 8 ``` apt install openjdk-8-jre-headless ```
* graphiz ``` apt install graphiz ```
* mysql jdbc drivers ``` apt install libmysql-java ```
  * directories to find drivers: ``` /usr/share/java ```


Running SchemaSpy
---
```bash
java -jar schemaspy6.jar -t mysql -dp /usr/share/java/mysql-connector-java.jar -db core -host localhost -s core -u [user] -p [password]  -o [html-files-output-directory]
```

Server 
---
Google GPC Instance on the test environment domain. Server is associated with the Data-Modeler project
The server can be accessed via the command line using the Google cloud CLI console app (gcloud)
Find more info on the [gcloud CLI](https://cloud.google.com/compute/docs/instances/connecting-to-instance) 

> connecting
> ```
> gcloud compute --project "carlib-data-modeler" ssh --zone "us-central1-c" "schemaspy-001"
>  ```


Manual process to generate SchemaSpy output
---
1.  Clone this project
1.  cd bin directory in project folder
1.  run ``` php schema_builder.php ```
    you see warning about "table exists..." ignore it
1.  Open you mysql workbench and export the database that was just created as a self-contained file.  You can also do this via the CLI
with mysqldump.  Your choice
1.  Upload the dump .sql file to the Data Modler CPC instance using the gcloud CLI or other method
1.  Login to the GPC instance then perform the following steps
    1.  Import the sql DDL into mysql.  run ``` mysql -u root -p [password] < [name/location of sql file just uploaded]```
    1.  Execute schema spy over over the imported database using the CLI command mentioned above under "Running Schema Spy"
    1.  Once the SchemaSpy assets have been created you can download them to your local machine.  

**Note:** This process should be automated so that an authorized user can click a button on a web app or within the
google sheet and generate the assets/docs
