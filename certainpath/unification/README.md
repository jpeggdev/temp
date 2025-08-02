# unification
A data warehouse platform that collects, normalizes, stores, and disseminates data from various sources to downstream targets.

# Running Tests

- Copy .env.test.local and .env.local from this [directory](https://yoursgi-my.sharepoint.com/:f:/g/personal/cholland_mycertainpath_com/EgcE95xUTrdPrhOebznREncB3T6T7bFRy-Q_oBTPqy0cWg?e=9hvwgT).
- Make sure you are on the VPN
- do an rm -rf var/tmp/*
- ./vendor/bin/phpunit
  - the suite can take 30 minutes to run.
  - we'll eventually mock some things out to speed this up.
- running tests from your IDE does work, but it appears to take longer

# Production

## Deployment

Unification relies on [Deployer](https://deployer.org/) for automated remote deployment. The Deployer command is installed via Composer.

- For local deployment (to test the deployment procedure).
  - Checkout the branch you wish to deploy and pull in remote changes.
  - Run: BRANCH=[branch] php vendor/bin/dep deploy localhost
    - The BRANCH parameter indicates the branch you wish to deploy, and should be the same as the branch you have checked out.
    - Example: BRANCH=my_branch php vendor/bin/dep deploy localhost
  - This will create a collection of local directories mimicking a deployment collection.

- For production deployment:
  - Checkout the branch you wish to deploy and pull in remote changes. This branch should be a fully vetted release branch.
  - Run: BRANCH=[branch] php vendor/bin/dep deploy nexus.mycertainpath.com
    - The BRANCH parameter indicates the branch you wish to deploy, and should be the same as the branch you have checked out.
    - Example: BRANCH=2024.09D php vendor/bin/dep deploy nexus.mycertainpath.com
  - This will deploy to the production server nexus.mycertainpath.com.

Deployer will take the following actions which include manipulating the file structure and executing database migrations. These actions are detailed in [deployer.php] recipe found in the project root.
- task deploy:setup
- task deploy:lock
- task deploy:release
- task deploy:update_code
- task deploy:shared
- task deploy:writable
- task deploy:vendors
- task unification:publish-tailwind
- task unification:compile-asset-map
- task unification:migrate
- task unification:warmup
- task deploy:symlink
- task deploy:unlock
- task deploy:cleanup
- task deploy:success

# Operations

## Obtain a CSV Output of all Prospects for a Given Company

- Obtain the API Key, referenced below as API_KEY.
- Choose a Company Identifier, aka "Intacct ID", from the [Company Roster](https://yoursgi-my.sharepoint.com/:x:/g/personal/cholland_mycertainpath_com/EXTR6gGt9bxLoJWlumlYS_8BKDNWdtFdQyaWZglyILaXkg?e=rdi0wv)
  - Example below: **AT07838**
- For Prospects Only:
  - https://nexus.mycertainpath.com/api/company/AT07838/prospects/csv?apiKey=API_KEY&customerInclusion=prospects_only&jobNumber=Week-1&ringTo=1-800-123-4567&versionCode=Version-1234&csr=Leo
- For Customers Only:
  - https://nexus.mycertainpath.com/api/company/AT07838/prospects/csv?apiKey=API_KEY&customerInclusion=customers_only&jobNumber=Week-1&ringTo=1-800-123-4567&versionCode=Version-1234&csr=Leo
- For Prospects and Customers:
  - https://nexus.mycertainpath.com/api/company/AT07838/prospects/csv?apiKey=API_KEY&customerInclusion=prospects_and_customers&jobNumber=Week-1&ringTo=1-800-123-4567&versionCode=Version-1234&csr=Leo

## Obtain a CSV Output of all Prospects for a Given Batch
- Obtain the API Key, referenced below as API_KEY.
- Choose a Batch ID, from the batch table
  - Example below: **1**
- For Prospects Only (excludes everyone who became a customer in the middle of the campaign for the given batch):
  - https://nexus.mycertainpath.com/api/batch/1/prospects/csv?apiKey=API_KEY&customerInclusion=prospects_only&jobNumber=Week-1&ringTo=1-800-123-4567&versionCode=Version-1234&csr=Leo
- For Customers Only (shows everyone who became a customer in the middle of the campaign for the given batch):
  - https://nexus.mycertainpath.com/api/batch/1/prospects/csv?apiKey=API_KEY&customerInclusion=customers_only&jobNumber=Week-1&ringTo=1-800-123-4567&versionCode=Version-1234&csr=Leo
- For Prospects and Customers (show the original set of prospects at the time of the batch creation):
  - https://nexus.mycertainpath.com/api/batch/1/prospects/csv?apiKey=API_KEY&customerInclusion=prospects_and_customers&jobNumber=Week-1&ringTo=1-800-123-4567&versionCode=Version-1234&csr=Leo

## Create a New Campaign for a Company Using Commands

Copy and paste the following command into your terminal, replacing the placeholders with actual values:
  - <> - Required Argument;
  - [<>] - Optional Argument;
  - Date Format: Y-m-d (Example: 2024-12-31);

    ```bash 
      bin/console unification:campaign:init <name> <startDate> <endDate> <mailingFrequencyWeeks> <companyId> <mailPackageId> [<description>] [<phoneNumber>]

## Create a New Campaign for a Company Using API Endpoints

- Obtain the API Key, referenced below as API_KEY.
- Use an API Client of your choice (e.g. Postman / Insomnia) to send a POST request to the following endpoint:
  - https://nexus.mycertainpath.com/api/campaign/create?apiKey=API_KEY
- Include the following fields in the request:
  ```json
  {
    "name": "Camapign Name",
    "description": "Campaign Description",
    "startDate": "YYYY-MM-DD",
    "endDate": "YYYY-MM-DD",
    "mailingFrequencyWeeks": 1,
    "companyId": 1,
    "mailPackageId": 1
  }

## Process Next Iteration (create a new set of batches) for a Company using a Command
  Copy and paste the following command into your terminal, replacing the placeholders with actual values:
  - [<>] - Optional Argument;
  - Date Format: Y-m-d (Example: 2024-12-31);
  - Note: If [<iterationStartDate>] is not provided, today's date will be used. 

    ```bash 
    bin/console unification:campaign:process-next-iteration [<iterationStartDate>]
    
# Development

## Ingest prospects data in bulk from Stochastic Roster Spreadsheet

- bin/console unification:data:bulk-migration --limit=2
  - The Master Spreadsheet is curated [here](https://yoursgi-my.sharepoint.com/:x:/g/personal/cholland_mycertainpath_com/EXTR6gGt9bxLoJWlumlYS_8BABbVlzA5g9SjrV6mk0H9pA?e=C6ZAAQ).
  - We occasionally upload it to S3 here:
    - [s3://stochastic-files/roster/Stochastic Active Clients - All-2024-09-23-17-04-58.xlsx](https://us-east-1.console.aws.amazon.com/s3/object/stochastic-files?region=us-east-1&bucketType=general&prefix=roster/Stochastic%20Active%20Clients%20-%20All-2024-09-23-17-04-58.xlsx)
  - The command iterates through every line in the sheet up to the limit and:
    - extracts the ".dbf" file path
    - downloads the file.
    - feeds it to MigrationService
  - At the end of the run, the command will output to the console a table similar to that of MigrateCommand.

## Dependencies

- https://csvkit.readthedocs.io/en/latest/
  - brew install csvkit (Mac)
- ext-dbase
  - pecl install dbase

## Local Database Setup

- ./provision_databases
  - This command DROPS and recreates users and databases to start fresh. DO NOT USE IN PRODUCTION.
- bin/console doctrine:migrations:migrate
  - Execute the stored migrations to populate the newly created database.

## Populate Local Working Data
- bin/console unification:populate-data


## Docker Setup Instructions

1. **Get the `.env` or `.env.local` file**: Contact the dev team to obtain these files.

2. **Build the Docker containers**:
   ```bash
   docker-compose build
3. **Start the containers:**:
   ```bash
   docker-compose up -d
4. **Create the database (Run from within docker container):**:
   ```bash
   bin/console doctrine:database:create
5. **Run migrations (Run from within docker container):**:
   ```bash
   bin/console doctrine:migrations:migrate
6. **Populate dummy data (Run from within docker container):**:
   ```bash
   bin/console unification:populate-data
7. **Build tailwind (Run from within docker container) **:
   ```bash
   bin/console tailwind:build
8. **Visit: http://localhost:8002/
