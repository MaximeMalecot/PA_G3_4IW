### Getting started

```bash
docker-compose build --pull --no-cache
docker-compose up -d
npm install
npm run watch
```

```
# URL
https://127.0.0.1

# Env DB
DATABASE_URL="postgresql://postgres:password@db:5432/db?serverVersion=13&charset=utf8"
```

```
# Set DB
PHP Docker :

php bin/console d:d:c
php bin/console d:s:u --force
```
