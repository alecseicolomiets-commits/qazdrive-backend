# QAZ DRIVE — Backend API

REST API backend для платформы аукциона автомобилей QAZ DRIVE.

## Технологии
- Laravel 11 (PHP 8.4)
- MySQL 8.0
- JWT Authentication (tymon/jwt-auth)
- Docker + Docker Compose

## Быстрый старт (Docker)

```bash
cp .env.example .env
docker-compose up --build
```

## Быстрый старт (локально)

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
php artisan migrate --seed
php artisan serve --port=8000
```

## API Base URL
http://127.0.0.1:8000/api

## Документация эндпоинтов

| Метод | URL | Auth | Описание |
|-------|-----|------|----------|
| POST | /api/auth/login | ❌ | Вход |
| POST | /api/auth/register | ❌ | Регистрация |
| POST | /api/auth/logout | ✅ | Выход |
| GET | /api/cars | ❌ | Список авто |
| GET | /api/cars/{id} | ❌ | Детали авто |
| POST | /api/cars/{id}/bid | ✅ | Сделать ставку |
| GET | /api/user/profile | ✅ | Профиль |
| PUT | /api/user/profile | ✅ | Обновить профиль |
| POST | /api/user/avatar | ✅ | Загрузить аватар |
| GET | /api/user/my-bids | ✅ | Мои ставки |
| GET | /api/brands | ❌ | Бренды |
| GET | /api/stats | ❌ | Статистика |
Тестовые аккаунты
РольЭлектронная почтаПарольАдминadmin@drive.kzadmin123Пользовательuser@drive.kzuser123