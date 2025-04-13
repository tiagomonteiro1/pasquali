# pasquali
Teste Lavavel

Primeiro, vamos criar um novo projeto Laravel:

composer create-project laravel/laravel travel-order-service
cd travel-order-service



Instalação de Pacotes Necessários

composer require tymon/jwt-auth
composer require laravel/sanctum
composer require guzzlehttp/guzzle

Executando o Projeto
Construa e inicie os containers Docker:

docker-compose up -d --build

Execute as migrações e seeds:

docker-compose exec app php artisan migrate --seed

Execute os testes:

docker-compose exec app php artisan test

Documentação da API
Autenticação
POST /api/register - Registrar novo usuário

POST /api/login - Login de usuário existente

Pedidos de Viagem (requer autenticação)
GET /api/orders - Listar todos os pedidos (filtros: status, destination, start_date, end_date)

POST /api/orders - Criar novo pedido de viagem

GET /api/orders/{id} - Visualizar pedido específico

POST /api/orders/{id}/status - Atualizar status do pedido (apenas por outros usuários)

POST /api/orders/{id}/cancel - Cancelar pedido aprovado (apenas pelo solicitante)
