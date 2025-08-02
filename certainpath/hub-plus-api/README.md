# Hub Plus API

## Deploy to Server

- Host: hub.mycertainpath.com
- vendor/bin/dep deploy branch={git branch}
- vendor/bin/dep deploy

## Cron Jobs

```shell
### Run GenerateMonthlyBalanceSheetCommand every day at 5:00 AM
0 5 * * * /usr/bin/php /home/deployer/hub-qa-api.mycertainpath.com/bin/console app:quickbooks:generate-monthly-balance-sheet >> /dev/null 2>&1

### Run GenerateProfitAndLossCommand every day at 5:00 AM
0 5 * * * /usr/bin/php /home/deployer/hub-qa-api.mycertainpath.com/bin/console app:quickbooks:generate-profit-and-loss >> /dev/null 2>&1

### Run GenerateTransactionListCommand every day at 5:00 AM
0 5 * * * /usr/bin/php /home/deployer/hub-qa-api.mycertainpath.com/bin/console app:quickbooks:generate-transaction-list >> /dev/null 2>&1

### Run bin/console hub:postage:process-sftp-directory ../../usps/sftp every day at 9am Central Time
0 14 * * * /usr/bin/php /home/deployer/hub-qa-api.mycertainpath.com/bin/console hub:postage:process-sftp-directory /home/usps/sftp >> /dev/null 2>&1
```

## Sync Company Roster from CARA and Stochastic Spreadsheet

- For CARA:
  - bin/console hub:company:ingest --source=account_application
- For Stochastic Roster:
  - bin/console hub:company:ingest --source=stochastic_roster

## Setup Instructions

1. **Get the `.env` or `.env.local` file**: Contact the dev team to obtain these files.

2. **Build the Docker containers**:
   ```bash
   docker-compose build
   ```
3. **Start the containers:**:
   ```bash
   docker-compose up -d
   ```
4. **Install PHP dependencies:**:
   ```bash
   composer install
   ```
5. **Create the database:**:
   ```bash
   bin/console doctrine:database:create
   ```
6. **Run database migrations:**:
   ```bash
   bin/console doctrine:migrations:migrate
   ```
7. **Load fixtures:**:
   ```bash
   ./bin/console doctrine:fixtures:load --append
   ```

8. ** Initialize Trades: **:
   ```bash
   bin/console hub:trades:initialize
   ```

9. ** Initialize Field Services Software: **:
   ```bash
   bin/console hub:software:initialize
   ```

10. ** Initialize Campaign Product Taxonomy: **:
   ```bash
   bin/console hub:campaign-products:initialize
```

## Setup Local Ingest Database

- run the following command to create the database:
  - ./provision_ingest_databases
- This will create both databases:
  - unification_ingest_generic_test <-- used by tests
  - unification_ingest_generic
- Run: (these get run automatically in the test harness on the test database)
  - ./sql/invoices_stream.sql
  - ./sql/members_stream.sql
  - ./sql/prospects_stream.sql

## Optional: Getting a local CARA Instance

In some cases you may need to run a local CARA instance to run the import commands.
To do this, follow these steps:

- clone the repo https://github.com/serviceline/account-app
- No need to composer install anything, you just want the "create-db" file
- cd account-app
  - ./create-db
- psql -hlocalhost -Ucertainpath certainpath < certainpath-20240919000001.sql
  - download certainpath-20240919000001.sql from [here](https://yoursgi-my.sharepoint.com/:u:/r/personal/cholland_mycertainpath_com/Documents/cara-db-dumps/certainpath-20240919000001.sql?csf=1&web=1&e=KGOEn4).
- psql -hlocalhost -Ucertainpath certainpath < cara-sandboxes.sql
  - download cara-sandboxes.sql from [here](https://yoursgi-my.sharepoint.com/:u:/r/personal/cholland_mycertainpath_com/Documents/cara-sandboxes.sql?csf=1&web=1&e=DtJHCZ).

## Visiting Hasura Console
http://localhost:8111/console
- password: myadminsecretkey

## Run Hasura Docker
- ```bash
  docker compose --file ./docker-compose-native.yml up graphql-engine
    ```

# Code Quality

## To maintain code quality, use the following tools:

1. **Run static analysis and detect potential bugs or type errors:**:
   ```bash
   vendor/bin/phpstan analyse
   ```
2. **Run php cs fixer for Symfony code style and coding standards violations:**:
   ```bash
   vendor/bin/php-cs-fixer fix --dry-run --diff
   ```
3. **Automatically fix Symfony code style and coding standards violations:**:
   ```bash
   vendor/bin/php-cs-fixer fix
   ```


### Make sure to resolve any issues that are flagged by PHPStan for better code quality and type safety.
