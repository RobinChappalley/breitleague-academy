# Breitling Academy Docker Setup

This repository contains a Docker setup for running the Breitling Academy application with Laravel backend, Vue.js frontend, and MySQL database.

## Requirements

- Docker
- Docker Compose

## Getting Started

### Building and Starting the Containers

1. Clone the repository
2. Navigate to the project directory
3. Copy the example environment file:

```bash
cp laravel/.env.docker.example laravel/.env.docker
```

4. Run the following command to build and start the containers:

```bash
docker-compose up -d
```

This will start three containers:
- `breitling-backend`: PHP container running the Laravel application
- `breitling-frontend`: Node.js container running the Vue.js application
- `breitling-mysql`: MySQL database

### Accessing the Applications

Once the containers are running, you can access:

- Laravel backend: `http://localhost:8000`
- Vue.js frontend: `http://localhost:5173`

### Running Artisan Commands

To run Artisan commands, you can use the following:

```bash
docker-compose exec backend php artisan <command>
```

For example, to run migrations:

```bash
docker-compose exec backend php artisan migrate
```

### Composer Commands

To run Composer commands:

```bash
docker-compose exec backend composer <command>
```

For example, to install dependencies:

```bash
docker-compose exec backend composer install
```

### NPM Commands for Frontend

To run NPM commands for the frontend:

```bash
docker-compose exec frontend npm <command>
```

For example, to build assets:

```bash
docker-compose exec frontend npm run build
```

## Container Details

### Backend (Laravel)

- PHP 8.2 with built-in server
- Composer
- Required PHP extensions for Laravel
- Runs on port 8000

### Frontend (Vue.js)

- Node.js
- NPM
- Vue.js development server
- Runs on port 5173

### MySQL

- MySQL 8.0
- Persistent data storage
- Runs on port 3306

## Environment Variables

The Laravel application uses environment variables defined in the `.env.docker` file. The Docker setup also uses environment variables defined in the `docker-compose.yaml` file.

## Troubleshooting

### Permission Issues

If you encounter permission issues, you may need to adjust the permissions in the container:

```bash
docker-compose exec backend chown -R www-data:www-data /var/www/storage
docker-compose exec backend chmod -R 775 /var/www/storage
```

### Database Connection Issues

If the application cannot connect to the database, ensure that:
1. The MySQL container is running (`docker-compose ps` should show `breitling-mysql` as "Up")
2. The environment variables in `.env.docker` match those in `docker-compose.yaml`
3. The database has been created and migrations have been run

### Backend Service Issues

If the backend service fails to start or you can't access it:

1. Check the logs for the backend container:
```bash
docker-compose logs backend
```

2. Ensure the Laravel application is properly configured:
```bash
docker-compose exec backend php artisan config:clear
docker-compose exec backend php artisan cache:clear
```

3. Verify that the APP_KEY is set in .env.docker:
```bash
docker-compose exec backend php artisan key:generate
```

### Frontend Service Issues

If the frontend service fails to start or you can't access it:

1. Check the logs for the frontend container:
```bash
docker-compose logs frontend
```

2. Ensure all dependencies are installed:
```bash
docker-compose exec frontend npm install
```

3. Restart the frontend service:
```bash
docker-compose restart frontend
```

### Hot Reload Issues

If hot reload isn't working for the frontend:

1. Make sure CHOKIDAR_USEPOLLING is set to true in the docker-compose.yaml
2. Check that your volumes are properly mounted
3. Try restarting the frontend container:
```bash
docker-compose restart frontend
```

## Stopping the Containers

To stop the containers:

```bash
docker-compose down
```

To stop the containers and remove volumes (this will delete all database data):

```bash
docker-compose down -v
```

## Additional Information

For more detailed information about the Vue.js frontend, please refer to the `VUE_README.md` file in the Laravel directory.
