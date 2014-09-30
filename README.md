Rate-it!
========

Introduction
------------
This is a thesis work, it's a general purpose polling platform.
It rely on the powerful dbms Neo4j to store the users informations
the surveys and the tags.




Installation
------------

- Prerequisites: Mysql, PHP5, Neo4j v2+ installed and configured.

Configuration:


create local versions of configuration files
in /config/autoload/
to do this, you have to modify some files

Step 1 - copy/move files:


    local.template.php > local.php
    database.local.template.php > database.local.php
    module.humus-neo4j-ogm.local.template.php > module.humus-neo4j-ogm.local.php
    scn-social-auth.local.template.php > scn-social-auth.local.php


and configure the variables on your needs.


Install vendor modules from composer:

    php composer.phar self-update
    php composer.phar install

(The `self-update` directive is to ensure you have an up-to-date `composer.phar`
available.)

Modified Vendor Modules
-----------------------


Extract and copy the modified vendor module "zfc-user"
located in the /ModifiedVendorModules/zfc-user.zip file
inside the vendor/zf-commons/zfc-user/ folder.

Database Settings
-----------------

Import the SQL database definition into your MySQL server.

    mysqldump -u [uname] -p[pass] --all-databases > database.structure.sql

database.structure.sql is located in the /config/autoload directory.

Another way to do that is throw the import function of PhpMyadmin (I suggest this way if you already got it)



Virtual Host
------------
Afterwards, set up a virtual host to point to the public/ directory of the
project and you should be ready to go!
