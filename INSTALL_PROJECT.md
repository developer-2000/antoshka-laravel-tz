# Antoshka - Система управления заказами и складом

Laravel 10 приложение для управления заказами, складом и интеграции с поставщиками.

## Требования

- Docker Desktop (для Windows) или Docker + Docker Compose
- Минимум 4GB свободной оперативной памяти

## Структура контейнеров

| Сервис | Порт | Описание |
|--------|------|----------|
| Nginx | 80 | Веб-сервер |
| MySQL | 3306 | База данных |
| Redis | 6379 | Очереди и кеш |
| phpMyAdmin | 8080 | Управление БД |
| Queue Worker | - | Обработка очередей (автоматический запуск) |

---

## Установка и запуск проекта

### 1. Клонирование репозитория

```bash
git clone <url-репозитория>
cd antoshka-tz
```

### 2. Настройка окружения

Создайте файл `.env` в корне проекта и настройте следующие параметры:

```env
APP_NAME=Antoshka
APP_KEY=base64:4GvbTeGZUj7lLKDNtkiL8Yf2rV7Ycaddn4scMGirCXo=
APP_URL=http://localhost

APP_ENV=local
APP_DEBUG=true

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# База данных MySQL
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=antoshka-tz
DB_USERNAME=root
DB_PASSWORD=

# Redis для очередей и кеша
REDIS_CLIENT=predis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Очереди через Redis
QUEUE_CONNECTION=redis

# Кеш через Redis
CACHE_DRIVER=redis

# Сессии через Redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

BROADCAST_DRIVER=log
FILESYSTEM_DISK=local

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

**Важно:** Убедитесь, что в `.env` файле указаны:
- `DB_HOST=mysql` (имя контейнера MySQL)
- `REDIS_HOST=redis` (имя контейнера Redis)
- `QUEUE_CONNECTION=redis`
- `CACHE_DRIVER=redis`
- `SESSION_DRIVER=redis`

### 3. Запуск Docker контейнеров

```bash
docker-compose up --build -d
```

Эта команда:
- Соберёт образы PHP-FPM и Queue Worker
- Запустит все необходимые сервисы (MySQL, Redis, Nginx, PHP-FPM, phpMyAdmin, Queue Worker)
- Автоматически установит зависимости Composer (выполняется в entrypoint.sh при старте PHP контейнера)
- Настроит права доступа

**Примечание:**
- Composer зависимости устанавливаются автоматически при первом запуске PHP контейнера
- NPM зависимости нужно установить отдельно (см. следующий шаг)
- Миграции базы данных нужно выполнить вручную (см. раздел 6)

### 4. Установка NPM зависимостей и сборка фронтенда

После запуска контейнеров установите NPM зависимости и соберите фронтенд:

```bash
docker-compose exec php npm install
docker-compose exec php npm run build
```

Или для разработки с hot-reload:

```bash
docker-compose exec php npm run dev
```

### 5. Доступ к приложению

После успешного запуска приложение будет доступно по адресам:

- **Основное приложение**: http://localhost
- **phpMyAdmin**: http://localhost:8080

### 6. Выполнение миграций и сидеров (обязательно)

**Важно:** После первого запуска обязательно выполните миграции с сидерами:

```bash
docker-compose exec php php artisan migrate:fresh --seed
```

Эта команда:
- Удалит все существующие таблицы
- Создаст таблицы заново
- Заполнит базу данных тестовыми данными из сидеров

## Управление контейнерами

### Запуск контейнеров

```bash
# Первый запуск (с пересборкой образов)
docker-compose up --build -d

# Обычный запуск
docker-compose up -d
```

### Остановка контейнеров

```bash
# Остановка и удаление контейнеров
docker-compose down

# Остановка и удаление контейнеров с volumes (удалит данные БД и Redis)
docker-compose down -v

# Остановка и удаление контейнеров, volumes и images
docker-compose down -v --rmi all
```

### Перезапуск контейнеров

```bash
# Перезапуск всех контейнеров
docker-compose restart

# Перезапуск конкретного контейнера
docker-compose restart php
docker-compose restart queue
docker-compose restart nginx
```

### Просмотр статуса

```bash
# Статус всех контейнеров
docker-compose ps

# Детальная информация
docker-compose ps -a
```

### Просмотр логов

```bash
# Логи всех сервисов
docker-compose logs -f

# Логи конкретного сервиса
docker-compose logs -f php
docker-compose logs -f queue
docker-compose logs -f nginx
docker-compose logs -f mysql
docker-compose logs -f redis

# Последние N строк логов
docker-compose logs --tail=100 php
```

## Работа с Composer

### Установка зависимостей

```bash
# Установка всех зависимостей
docker-compose exec php composer install

# Установка только production зависимостей
docker-compose exec php composer install --no-dev --optimize-autoloader

# Обновление зависимостей
docker-compose exec php composer update

# Обновление конкретного пакета
docker-compose exec php composer update vendor/package
```

### Другие команды Composer

```bash
# Просмотр установленных пакетов
docker-compose exec php composer show

# Поиск пакетов
docker-compose exec php composer search package-name

# Добавление нового пакета
docker-compose exec php composer require vendor/package

# Удаление пакета
docker-compose exec php composer remove vendor/package

# Очистка кеша Composer
docker-compose exec php composer clear-cache
```

## Работа с NPM

### Установка зависимостей

```bash
# Установка всех зависимостей
docker-compose exec php npm install

# Установка конкретного пакета
docker-compose exec php npm install package-name

# Установка dev зависимостей
docker-compose exec php npm install --save-dev package-name
```

### Сборка фронтенда

```bash
# Сборка для production
docker-compose exec php npm run build

# Dev режим с hot-reload (запускается и работает постоянно)
docker-compose exec php npm run dev
```

### Проверка безопасности NPM

```bash
# Проверка уязвимостей
docker-compose exec php npm audit

# Автоматическое исправление уязвимостей
docker-compose exec php npm audit fix

# Принудительное исправление (может сломать зависимости)
docker-compose exec php npm audit fix --force
```

### Другие команды NPM

```bash
# Обновление зависимостей
docker-compose exec php npm update

# Удаление пакета
docker-compose exec php npm uninstall package-name

# Просмотр установленных пакетов
docker-compose exec php npm list

# Просмотр устаревших пакетов
docker-compose exec php npm outdated
```

## Работа с Laravel Artisan

### Миграции

```bash
# Запуск миграций
docker-compose exec php php artisan migrate

# Откат последней миграции
docker-compose exec php php artisan migrate:rollback

# Откат всех миграций
docker-compose exec php php artisan migrate:reset

# Откат и повторный запуск миграций
docker-compose exec php php artisan migrate:refresh

# Откат, повторный запуск и заполнение данными
docker-compose exec php php artisan migrate:fresh --seed

# Просмотр статуса миграций
docker-compose exec php php artisan migrate:status
```

### Сидеры

```bash
# Запуск всех сидеров
docker-compose exec php php artisan db:seed

# Запуск конкретного сидера
docker-compose exec php php artisan db:seed --class=DatabaseSeeder

# Миграции + сидеры
docker-compose exec php php artisan migrate --seed
```

### Кеш

```bash
# Очистка всех кешей
docker-compose exec php php artisan cache:clear
docker-compose exec php php artisan config:clear
docker-compose exec php php artisan route:clear
docker-compose exec php php artisan view:clear

# Кеширование для production
docker-compose exec php php artisan config:cache
docker-compose exec php php artisan route:cache
docker-compose exec php php artisan view:cache

# Очистка всех кешей одной командой
docker-compose exec php php artisan optimize:clear
```

### Очереди

```bash
# Просмотр очереди (в контейнере queue это работает автоматически)
docker-compose exec queue php artisan queue:work redis --tries=3 --timeout=90

# Просмотр неудачных задач
docker-compose exec php php artisan queue:failed

# Повторный запуск неудачной задачи
docker-compose exec php php artisan queue:retry all

# Очистка неудачных задач
docker-compose exec php php artisan queue:flush

# Очистка всех задач из очереди
docker-compose exec php php artisan queue:clear redis
```

### Создание компонентов

```bash
# Создание контроллера
docker-compose exec php php artisan make:controller ControllerName

# Создание модели
docker-compose exec php php artisan make:model ModelName

# Создание миграции
docker-compose exec php php artisan make:migration create_table_name

# Создание сидера
docker-compose exec php php artisan make:seeder SeederName

# Создание Request валидации
docker-compose exec php php artisan make:request RequestName

# Создание Job
docker-compose exec php php artisan make:job JobName

# Создание Event
docker-compose exec php php artisan make:event EventName

# Создание Listener
docker-compose exec php php artisan make:listener ListenerName
```

### Другие полезные команды Artisan

```bash
# Просмотр всех доступных команд
docker-compose exec php php artisan list

# Просмотр версии Laravel
docker-compose exec php php artisan --version

# Генерация нового APP_KEY
docker-compose exec php php artisan key:generate

# Создание символической ссылки для storage
docker-compose exec php php artisan storage:link

# Просмотр текущей конфигурации
docker-compose exec php php artisan config:show
```

## Работа с базой данных

### Доступ к MySQL

```bash
# Вход в MySQL контейнер
docker-compose exec mysql mysql -u root -p

# Выполнение SQL команды
docker-compose exec mysql mysql -u root -p -e "SHOW DATABASES;"

# Импорт SQL файла
docker-compose exec -T mysql mysql -u root -p database_name < file.sql

# Экспорт базы данных
docker-compose exec mysql mysqldump -u root -p database_name > backup.sql
```

### Доступ к Redis

```bash
# Вход в Redis CLI
docker-compose exec redis redis-cli

# Просмотр всех ключей
docker-compose exec redis redis-cli KEYS "*"

# Очистка всех данных Redis
docker-compose exec redis redis-cli FLUSHALL

# Просмотр информации о Redis
docker-compose exec redis redis-cli INFO
```

## Работа с файлами и правами доступа

### Установка прав доступа

```bash
# Установка прав на storage и cache
docker-compose exec php chmod -R 775 storage bootstrap/cache

# Установка владельца
docker-compose exec php chown -R www-data:www-data storage bootstrap/cache

# Комбинированная команда
docker-compose exec php sh -c "chmod -R 775 storage bootstrap/cache && chown -R www-data:www-data storage bootstrap/cache"
```

### Работа с файлами внутри контейнера

```bash
# Вход в PHP контейнер
docker-compose exec php sh

# Вход в MySQL контейнер
docker-compose exec mysql sh

# Вход в Redis контейнер
docker-compose exec redis sh

# Копирование файла из контейнера
docker cp antoshka-php:/var/www/path/to/file ./local/path

# Копирование файла в контейнер
docker cp ./local/file antoshka-php:/var/www/path/
```

## Очистка и обслуживание

### Очистка Docker

```bash
# Удаление остановленных контейнеров
docker container prune

# Удаление неиспользуемых образов
docker image prune

# Удаление неиспользуемых volumes
docker volume prune

# Полная очистка (контейнеры, образы, volumes, сети)
docker system prune -a --volumes --force

# Удаление конкретного образа
docker rmi image-name

# Удаление конкретного volume
docker volume rm volume-name
```

### Пересборка образов

```bash
# Пересборка без кеша
docker-compose build --no-cache

# Пересборка конкретного сервиса
docker-compose build --no-cache php

# Пересборка и запуск
docker-compose up --build -d
```

## Мониторинг и отладка

### Просмотр использования ресурсов

```bash
# Использование ресурсов контейнерами
docker stats

# Использование ресурсов конкретным контейнером
docker stats antoshka-php
```

### Проверка здоровья контейнеров

```bash
# Проверка health check
docker-compose ps

# Детальная информация о контейнере
docker inspect antoshka-php

# Проверка логов health check
docker inspect --format='{{json .State.Health}}' antoshka-php
```

### Отладка

```bash
# Выполнение команды в контейнере
docker-compose exec php php -v
docker-compose exec php composer --version
docker-compose exec php npm --version

# Просмотр переменных окружения
docker-compose exec php env

# Проверка подключения к MySQL
docker-compose exec php php -r "try { new PDO('mysql:host=mysql;port=3306', 'root', ''); echo 'Connected'; } catch(Exception \$e) { echo 'Failed'; }"

# Проверка подключения к Redis
docker-compose exec php php -r "try { \$redis = new Redis(); \$redis->connect('redis', 6379); echo 'Connected'; } catch(Exception \$e) { echo 'Failed'; }"
```

## Тестирование

### Запуск всех тестов

```bash
# Запуск всех тестов
docker-compose exec php php artisan test

# Запуск всех тестов с подробным выводом
docker-compose exec php php artisan test --verbose
```

### Unit тесты

```bash
# Запуск всех Unit тестов
docker-compose exec php php artisan test --testsuite=Unit

# Запуск конкретного Unit теста
docker-compose exec php php artisan test tests/Unit/OrderTest.php
docker-compose exec php php artisan test tests/Unit/InventoryTest.php
docker-compose exec php php artisan test tests/Unit/SupplierServiceTest.php
docker-compose exec php php artisan test tests/Unit/StoreOrderRequestTest.php
```

### Feature тесты

```bash
# Запуск всех Feature тестов
docker-compose exec php php artisan test --testsuite=Feature

# Запуск конкретного Feature теста
docker-compose exec php php artisan test tests/Feature/OrderApiTest.php
docker-compose exec php php artisan test tests/Feature/InventoryApiTest.php
docker-compose exec php php artisan test tests/Feature/ReservationFlowTest.php
docker-compose exec php php artisan test tests/Feature/SupplierIntegrationTest.php
docker-compose exec php php artisan test tests/Feature/FullFlowTest.php
docker-compose exec php php artisan test tests/Feature/SupplierErrorHandlingTest.php
```

### Дополнительные опции

```bash
# Запуск тестов с фильтром по имени
docker-compose exec php php artisan test --filter=testMethodName

# Запуск тестов с остановкой на первой ошибке
docker-compose exec php php artisan test --stop-on-failure

# Запуск тестов с покрытием кода (требует установки Xdebug)
docker-compose exec php php artisan test --coverage

# Запуск тестов в параллельном режиме
docker-compose exec php php artisan test --parallel

# Запуск тестов с детальным выводом ошибок
docker-compose exec php php artisan test --testdox
```




