# Development Reference

### Basic Commands
Here are some basic commands to interact with the project:

- **Install Dependencies**
   ```bash
   composer install
   ```

- **Run Migrations**
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

- **Start the Server**
   ```bash
   symfony server:start
   ```
   Your application should now be running at `http://localhost:8000`.

- **Tailwind Build Watch**
   ```bash
   php bin/console tailwind:build --watch
   ```
  
- **Clear Cache**
  ```bash
  php bin/console cache:clear
  ```

- **Run Tests**
  ```bash
  php bin/phpunit
  ```

- **View Routes**
  ```bash
  php bin/console debug:router
  ```
