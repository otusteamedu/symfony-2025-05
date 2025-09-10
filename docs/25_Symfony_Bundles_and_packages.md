# Symfony Bundles и пакеты

Запускаем контейнеры командой `docker compose up -d`
Заходим в контейнер командой `docker exec -it php sh`. Дальнейшие команды выполняются из контейнера.
Перед занятием клонируем https://github.com/otusteamedu/statsd-bundle.git в statsd и удаляем все файлы..
Чтобы можно было быстро diff подсмотреть

## Выносим хранилище метрик в бандл
0. Показываем класс, который будем выносить `MetricsStorage`
1. Создаем директорию `statsd` в корне проекта (уже должна быть создана через git clone)
   - Рассказываем про 2 способа работать с бандлом - в отдельном окне IDE и внутри проекта в одном окне IDE
2. Если мы делаем не отдельным пакетом бандл, то можно обойтись без отдельного `composer.json`. Правим секцию `autoload` в  основном `composer.json`
    ```json
    "psr-4": {
        "App\\": "src/",
        "StatsdBundle\\": "statsdBundle/src"
    }
    ```
   Но мы хотим вынести бандл в отдельный пакет
1. Создаем `composer.json` (по кусочкам)
   Обращаем внимание на:
   - type: symfony-bundle
   - никаких лишних зависимостей
   - ограничнеия версия должны быть максимально мягкие
   - неймспейс
    ```json
    {
        "name": "statsd-bundle",
        "description": "Provides configured MetricsStorage to send metrics to graphite",
        "type": "symfony-bundle",
        "license": "MIT",
        "require": {
            "php": ">=8.3",
            "slickdeals/statsd": "^3.2"
        },
        "autoload": {
            "psr-4": {
                "StatsdBundle\\": "src/"
            }
        }
    }
    ```

2. Создаём файл `StatsdBundle\StatsdBundle`
    ```php
    <?php
    
    namespace StatsdBundle;
    
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
    use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
    
    class StatsdBundle extends AbstractBundle
    {
    }
    ```
   
3. Пробуем подключить
```shell
 composer require otusteamedu/statsd-bundle
```
Получаем ошибку: 
```
 Could not find a matching version of package otusteamedu/statsd-bundle. Check the package spelling, your version constraint a   
  nd that the package is available in a stability which matches your minimum-stability (stable).    
```

3. Добавляем репозиторий и объясняем зачем это нужно:
   ```json
   {
   "repositories": [
        {
            "type": "path",
            "url": "./statsd"
        }
    ]
   }
   ```
   Пробуем снова подключить и получаем ошибку:
   ```
   Could not find a version of package otusteamedu/statsd-bundle matching your minimum-stability (stable). Require it with an explicit version constraint allowing its desired stability.
   ```
3. Добавляем версию:
    ```json
    {
    "name": "otusteamedu/statsd-bundle",
    "version": "0.0.1",
    }
    ```
    Пробуем снова подключить. Успешно. Обращаем внимание на вывод:
    ```
     - Configuring otusteamedu/statsd-bundle (>=0.0.1): From auto-generated recipe
    ```
   Показываем `bundles.php` и обращаем внимание на то, что бандл подключился автоматически.
3. Тестируем, что работает. Создаем тестовый класс и в любом контроллере приложения проверям, что он работает:
    ```php
    <?php

   declare(strict_types=1);
   
   namespace StatsdBundle;
   
   class Hello
   {
   public static function world() {
   die('Hello, world!');
   }
   }
    ```
3. Показывааем как работает MetricsStorage
   В контроллере CreateUser/v2 создается пользователь, кидается событие `UserIsCreatedEvent`.  
   В `UserEventSubscriber` в методе `onUserIsCreated` вызывается метод `increment` класса `MetricsStorage`, который отправляет метрику в StatsD.

3. Добавляем класс `StatsdBundle\Storage\MetricsStorageInterface`
   - в бандлах принято выделять интерфейсы, чтобы можно было легко заменить реализацию
    ```php
    <?php
    
    namespace StatsdBundle\Storage;
    
    interface MetricsStorageInterface
    {
        public function increment(string $key, ?float $sampleRate = null, ?array $tags = null): void;
    }
    ```
4. Переносим класс `App\Infrastructure\Storage\MetricsStorage` и интерфейс в пространство имён `StatsdBundle\Storage`.
   Эти три константы специфичны для приложения, поэтому мы смотрим где они используются и переносим их туда.
    ```php
     public const USER_CREATED = 'user_created';
    public const CACHE_HIT_PREFIX = 'cache.hit.';
    public const CACHE_MISS_PREFIX = 'cache.miss.';
    ```
   Запускаем HTTP action для CreateUser/v2 и видим ошибку:
    ```
   Cannot autowire service "App\Domain\EventSubscriber\UserEventSubscriber": argument "$metricsStorage" of method "__construct()" references interface "StatsdBundle\Storage\MetricsStorageInterface" but no such service exists. Did you create a class that implements this interface?
   ```

5. В файле `config/services.yaml` в секции `services` убираем описание сервиса
   `App\Infrastructure\Storage\MetricsStorage`
   - Отмечаем преимущество PHP конфигов + rector позволяет мигрировать

6. Создаём файл `statsd/config/services.yaml`
   Вначале переносим как есть:
    ```yaml
   services:
     StatsdBundle\Storage\MetricsStorage:
       class: StatsdBundle\Storage\MetricsStorage
       arguments:
         - graphite
         - 8125
         - my_app
   
     StatsdBundle\Storage\MetricsStorageInterface: '@StatsdBundle\Storage\MetricsStorage'
   ```
  

   Пробуем контроллер дернуть, получим ошибку, так как конфиг не подключен.
8. Подключаем конфиг в `StatsdBundle\StatsdBundle`:
   ```php
   <?php
   
   namespace StatsdBundle;
   
   use Symfony\Component\DependencyInjection\ContainerBuilder;
   use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
   use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
   
   class StatsdBundle extends AbstractBundle
   {
       public function loadExtension(
           array $config,
           ContainerConfigurator $container,
           ContainerBuilder $builder
       ): void {
           $container->import('../config/services.yaml');
       }
   }
   ```
   Теперь должно быть успешно
12. Чтобы нам не мешало исключение unique user exception, меняем контроллер и убираем собственно создание юзера:
       - это для удобства демонстрации и не должно попасть в репозиторий
   ```php
   <?php
   
   namespace App\Controller\Web\CreateUser\v2;
   
   use App\Controller\Web\CreateUser\v2\Input\CreateUserDTO;
   use StatsdBundle\Storage\MetricsStorageInterface;
   use Symfony\Component\HttpFoundation\JsonResponse;
   use Symfony\Component\HttpKernel\Attribute\AsController;
   use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
   use Symfony\Component\Routing\Attribute\Route;
   
   #[AsController]
   class Controller
   {
       #[Route(path: 'api/v2/user', methods: ['POST'])]
       public function __invoke(
           #[MapRequestPayload] CreateUserDTO $createUserDTO,
           MetricsStorageInterface $metricsStorage,
       ): JsonResponse {
           $metricsStorage->increment('user_created');
           return new JsonResponse('ok');
       }
   }
   
   ```

14. Добрабатываем `services.yaml`, чтобы не завязываться на FQCN в качестве идентификаторов сервисов
   ```yaml
   services:
   
   statsd.metrics_storage:
     class: StatsdBundle\Storage\MetricsStorage
     arguments:
       - graphite
       - 8125
       - my_app
   
   StatsdBundle\Storage\MetricsStorageInterface:
     alias: 'statsd.metrics_storage'
   ```
   Здесь можно показать простейший декоратор, и переопределить сервис по алиасу



## Добавляем конфигурацию в бандл
1. Пробуем определить параметры и переопределить их в основном приложении.
   Проверяем через dd() прямо в `MetricsStorage`
   ```yaml
   parameters:
     statsd.port: 8125
   
   services:
     statsd.metrics_storage:
       class: StatsdBundle\Storage\MetricsStorage
       arguments:
         - graphite
         - '%statsd.port%'
         - my_app
   
     StatsdBundle\Storage\MetricsStorageInterface:
       alias: 'statsd.metrics_storage'
   ```
   Не очень удобно, нет валидации. Можно сделать конфиг как у серьезных бандлов

2. Добавляем `configure()` в класс `StatsdBundle\StatsdBundle`
    ```php
    <?php
    
    namespace StatsdBundle;
    
    use StatsdBundle\Storage\MetricsStorageInterface;
    use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
    use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
    
    class StatsdBundle extends AbstractBundle
    {
        public function configure(DefinitionConfigurator $definition): void
        {
            $definition->rootNode()
                ->children()
                    ->arrayNode('client')
                        ->children()
                            ->scalarNode('host')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->end()
                            ->scalarNode('port')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->end()
                            ->scalarNode('namespace')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->end()
                        ->end()
                    ->end()
                ->end();
        }
    
    
        public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
        {
            $container->import('../config/services.yaml');
        }
    }
    ```
3. Добавляем файл `config/packages/statsd.yaml`
    ```yaml
    statsd:
      client:
        host: '%env(STATSD_HOST)%'
        port: '%env(STATSD_PORT)%'
        namespace: '%env(STATSD_NAMESPACE)%'
    ```
4. Добавляем переменные окружения в файл `.env`
    ```dotenv
   STATSD_HOST=localhost
   STATSD_PORT=8125
   STATSD_NAMESPACE=my_app
   ````
5. Тестируем валидацию. Комментируем какой-то из параметров конфига и наблюдаем ошибку.
6. Еще фишки DSL конфига:
   - валидация произвольная:
      ```php
      ->validate()
        ->ifTrue(function ($v) { return $v <= 0; })
        ->thenInvalid('Number must be positive')
      ->end()
      ```
   - можно указать дефолтное значение:
      ```php
      ->scalarNode('port')
        ->defaultValue(8125)
      ```
   - можно задепрекейтить:
        ```php
        ->scalarNode('port')
            ->defaultValue(8125)
            ->setDeprecated('Use "client.port" instead')
        ```
7. Показываем использование конфига прямо в StatsdBundle:
   ```php
   public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
   {
      $container->import('../config/services.yaml');
   
      $container->services()
          ->get('statsd.metrics_storage')
          ->arg(0, $config['client']['host'])
          ->arg(1, $config['client']['port'])
          ->arg(2, $config['client']['namespace']);
   }
   ```


## Выносим бандл в отдельный репозиторий и добавляем рецепт
Копирование конфига и переменных можно вынести в рецепт.
Готовый репозиторий с рецептом: https://github.com/otusteamedu/symfony-recipes
https://symfony.com/doc/current/setup/flex_private_recipes.html

Тут показываем рецепт в репе.
А так же подключение в extras в composer.json
Удляем конфиг и из bundles.php, возвращаем в репозиториях композера ссылку на гитхаб и ствим снова
Обращаем внимание на то что рецепт подключился


1. Создаём новый репозиторий для рецептов в GitHub и клонируем его локально
2. В новом репозитории создаём файл `statsd.bundle.1.0.json`
    ```json
    {
      "manifests": {
        "otusteamedu/statsd-bundle": {
          "manifest": {
            "bundles": {
              "StatsdBundle\\StatsdBundle": ["all"]
            },
            "copy-from-recipe": {
              "config/": "%CONFIG_DIR%"
            },
            "env": {
              "STATSD_HOST": "graphite",
              "STATSD_PORT": "8125",
              "STATSD_NAMESPACE": "my_app"
            }
          },
          "files": {
            "config/packages/statsd.yaml": {
              "contents": [
                "statsd:",
                "  client:",
                "    host: %env(STATSD_HOST)%",
                "    port: %env(STATSD_PORT)%",
                "    namespace: %env(STATSD_NAMESPACE)%"
              ],
              "executable": false
            }
          },
          "ref": "35e18ca78b9718d2afca62b3ec670ad36e77195c"
        }
      }
    }
    ```
3. В новом репозитории создаём файл `index.json`
    ```json
    {
      "recipes": {
        "otusteamedu/statsd-bundle": [
          "1.0"
        ]
      },
      "branch": "main",
      "is_contrib": true,
      "_links": {
        "repository": "github.com/otusteamedu/statsd-bundle/",
        "origin_template": "{package}:{version}@github.com/otusteamedu/statsd-bundle:main",
        "recipe_template": "https://api.github.com/repos/otusteamedu/symfony-recipes/contents/statsd.bundle.1.0.json"
      }
    }
    ```
4. Пушим файлы в удалённый репозиторий
5. Создаём новый репозиторий для бандла в GitHub и переносим в него всё содержимое каталога `statsdBundle` из основного
   репозитория проекта
6. В основном репозитории проекта
   1. Убираем загрузку бандла `StatsdBundle` из файла `config/bundles.php`
   2. Удаляем файл `config/packages/statsd.yaml`
7. В новом репозитории создаём в корне файл `composer.json`
    ```json
    {
      "name": "otusteamedu/statsd-bundle",
      "description": "Provides configured MetricsStorage to send metrics to graphite",
      "type": "symfony-bundle",
      "license": "MIT",
      "require": {
        "php": ">=8.3",
        "slickdeals/statsd": "^3.2"
      },
      "autoload": {
        "psr-4": {
          "StatsdBundle\\": "src/"
        }
      }
    }
    ```
8. Пушим новый проект в репозиторий
9. Создаём тэг `1.0`
10. В основном репозитории проекта в файле `composer.json`
    1. исправляем секцию `autoload`
        ```json
        "psr-4": {
            "App\\": "src/"
        }
        ```
    2. в секцию `extra.symfony` добавляем новый ключ
        ```json
        "endpoint": [
            "https://api.github.com/repos/otusteamedu/symfony-recipes/contents/index.json",
            "flex://defaults"
        ]
        ```
    3. добавляем секцию `repositories`
        ```json
        "repositories": [
            {
                "type": "vcs",
                "url": "git@github.com:otusteamedu/statsd-bundle.git"
            }
        ]
        ```
11. В основном репозитории проекта удаляем пакет `slickdeals/statsd`
12. Устанавливаем пакет `otusteamedu/statsd-bundle`, соглашаемся на выполнение рецепта
13. Выполняем ещё один запрос Add user v2 из Postman-коллекции v10 и проверяем, что данные всё ещё поступают в Graphite


### Extra Advice
- Всегда ведем README.MD
- CHANGELOG.MD
- SEMVER и публикация версий
- Теги как точка расширения:
  - https://habr.com/ru/post/499074/
- Тестирование интеграционное через микроприложение:
  - https://habr.com/ru/articles/500044/