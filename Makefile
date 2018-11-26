dev:
	@docker-compose down && \
		docker-compose build --pull --no-cache && \
		docker-compose \
			-f docker-compose.yml \
		up -d --remove-orphans

down:
	@docker-compose down

stopall:
	docker stop $(docker ps -a -q)
	docker rm $(docker ps -a -q)

show:
	docker ps -a