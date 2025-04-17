## Descrição

API que recebe um arquivo CSV com dados de usuários e cadastra no banco de dados utilizando um sistema de fila.

## Endpoints
```
[POST] /api/upload
[GET] /api/import-status/{id}
[GET] /api/users
```

Uma collection do Postman está disponível na raíz do projeto para melhor visualização: ``users_importer_postman_collection.json``.

Um exemplo de arquivo CSV está disponível na raíz do projeto: ``users-data.csv``.

## Requisitos
- Docker e Docker Compose

## Especificações
- PHP 8.4.6
- MySQL 8.0
- Laravel 12.0
- Redis 7.4


## Instalação
#### 1. Faça uma cópia do arquivo .env.example e renomeie para .env;
#### 2. Ajuste o .env conforme a necessidade;
#### 3. Crie os containeres PHP e MySQL:

```bash
docker-compose up -d --build
```
- OBS.: Em alguns ambientes, o comando pode ter sido instalado como `docker compose` (sem hífen).

#### 4. Rode as migrations:
```bash
docker exec -it users-importer-app php artisan migrate
```

#### 5. Deixe rodando o processamento das filas:
```bash
docker exec -it users-importer-app php artisan queue:work
```

## Fluxo e explicação técnica
### Geral
- Foi utilizado o recurso de idiomas (lang) do Laravel nas mensagens de retorno, disponíveis em Inglês (en) e Português Brasileiro (pt_BR), podendo ser alterado no arquivo `.env` na variável `APP_LOCALE`;

- Criado o middleware `FormatApiResponse`, para estabelecer um padrão no JSON de retorno de todos os endpoints definidos no grupo de rotas **api**, disponíveis no arquivo `routes/api.php`;

- Utilizando o provider ``AppServiceProvider``, foi criado um log simples para registrar quando um job é processado e finalizado;

- Todos os logs podem ser encontrados no arquivo padrão do Laravel: `storage/logs/laravel.log`.

### Endpoint ``/api/upload``
1. Deve ser enviado um CSV (com a chave "file") de até 10 MB (limitação que pode ser alterada conforme os recursos computacionais disponíveis);

2. Realizará as devidas validações e, se estiver tudo certo, irá dividir os usuários em lotes de 500 (também pode ser facilmente alterado), visando inserções parciais para evitar problemas em caso de muitos usuários. 
Cada lote terá seu próprio job e será colocado na fila no Redis para inserção assíncrona;

3. Salvará o arquivo no diretório ``storage/app/private``, para seguir o novo padrão recomendado do Laravel 12.x;

4. No retorno do upload, é disponibilizado o array ``data`` contendo os IDs dos jobs para consulta posterior, utilizando o endpoint ``/api/import-status/{id}``.

### Endpoint ``/api/import-status/{id}``
1. O ID deve ser um número inteiro positivo. É possível saber o ID de cada importação através do retorno do endpoint ``/api/upload``, no array ``data``.

### Endpoint ``/api/users``
1. Sem parâmetros obrigatórios, mas é possível paginar os resultados por meio do parâmetro `?page=2`

## Alguns exemplos de melhorias futuras...
- Implementação de alguns Design Patterns, como por exemplo:
    - Repository para encapsulamento da camada responsável pela interação com o banco;
    - Strategy para facilmente gerenciar futuros formatos de arquivos, como .xlsx, .txt e outros;
    - Observer para monitorar as filas e ajudar no seu gerenciamento.
    
    ... Dentre outras que possam vir a fazer sentido futuramente;
- Testes unitários, de integração, funcionais e outros;
- Implementação de Cron e Scheduler, para reprocessar a cada X tempo os jobs que falharam;
- Desenvolver ou integrar uma ferramenta mais robusta para análise de logs e jobs (como o Laravel Horizon);
- Autenticação JWT ou com Laravel Sanctum;
- Adaptação do código para suportar threads, para processamento dos dados em paralelo quando arquivos muito grandes;
- Implementação de UUID para identificação única, virtualmente ilimitada e mais **segura** (em caso de rotas desprotegidas que passam despercebidas e é possível acessá-las por meio do ID sequencial);
- Exceptions personalizadas para cada tipo de erro.


## Declarações finais
Um projeto bem interessante para teste de conhecimentos, pois apesar de parecer simples, precisa ser bem feito para alcançar a performance desejada, exigindo conhecimentos de nível médio/avançado. 

No dia a dia das empresas, uma aplicação como a deste projeto (ou mesmo um CRUD) pode ser desenvolvida rapidamente e de forma simples, mas também pode ser feita de maneira sofisticada e mais elaborada, sendo o ideal avaliar a necessidade e o contexto de cada demanda (o bom e velho "depende").

Por conta do meu trabalho atual que está demandando mais de 8h por dia e o prazo curto para finalização do teste, não consegui dedicar tanto tempo quanto gostaria para aprimorar o projeto, mas espero que tenha conseguido atingir o esperado.