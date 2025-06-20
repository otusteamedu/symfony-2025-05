COMPOSE       := docker compose -f docker-compose.yml --env-file .env
DOCKER_EXEC := ${COMPOSE} exec php-fpm

# Старт контейнера
up:
	${COMPOSE} up -d

# Остановка контейнера и удаление всех данных
down:
	${COMPOSE} down

# Вход в shell контейнера с php
cli:
	${COMPOSE} exec php-fpm  bash

test:
	${DOCKER_EXEC} ./vendor/bin/phpunit -c sharedKernel/tests/phpunit.xml --testsuite=unit --testdox
	${DOCKER_EXEC} ./vendor/bin/phpunit -c sales/tests/phpunit.xml --testsuite=unit --testdox

# Uncovered - a dependency that was found when collecting all dependencies, but that is not covered by architecture rules, i.e. is never a depend dependee.
# Unassigned - a dependency, which is not assigned to any layer.
deptrac:
	${DOCKER_EXEC} ./vendor/bin/deptrac analyze --config-file=sales/deptrac-layers.yaml --fail-on-uncovered --report-uncovered
#	${DOCKER_EXEC} ./vendor/bin/deptrac analyze --config-file=sales/deptrac-layers.yaml --fail-on-uncovered --report-uncovered \
#		--cache-file=/tmp/.deptrac-sales-layers.cache



