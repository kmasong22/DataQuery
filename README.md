# DataQuery
A Very Simple ORM for PHP for postgreSQL, MySQL, Microsoft SQL Server, and SQLite

## How To Use
Just import the class.dqx.php into your codes and start using

## Supported SQL Servers
* PostgreSQL
* MySQL
* MariaDB
* Microsoft SQL Server
* SQLite

## Supported Functions
* SELECT
* UPDATE
* INSERT
* DELETE

## Directives
* sqlQuery -> Used to create custom SQL Query
* sqlSelect -> Used to do a Select function
* sqlFrom -> Used to specify which table to fetch
* sqlJoin -> Used to do joins
* sqlWhere -> Used to specify conditions, used in SELECT, UPDATE, and DELETE function
* sqlSort -> Used to order query results
* sqlGroupBy -> Used to group query results for aggregation
* sqlHaving -> Used to specify HAVING function within a query
* sqlLimit -> Used to limit result size
* sqlInsertInto -> Used to initialize an INSERT function
* sqlUpdate -> Used to initialize an UPDATE function
* sqlDeleteFrom -> Used to initialize an DELETE function
* sqlSet -> Used to specify values to INSERT or UPDATE a single row
* sqlSetMulti -> Used to specify values to INSERT or UPDATE a multiple row
