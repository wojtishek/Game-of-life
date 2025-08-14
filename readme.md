# Game of Life

## Prerequisites
- Docker
- Docker Compose
- Git

## Run
1. Clone the repository
2. Run `docker compose up -d`
3. Run `docker compose exec app composer install`
4. Visit `http://localhost:8181` in your browser

## Testing
- Run tests with: `docker compose exec app composer test`

## Code quality
- Run static analysis: `docker compose exec app composer phpstan`
- Run code style check: `docker compose exec app composer cs-check`