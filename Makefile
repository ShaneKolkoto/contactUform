.PHONY: up down logs shell test rebuild fresh db

up:
	docker compose up -d

down:
	docker compose down

logs:
	docker compose logs -f web db

shell:
	docker compose exec web bash

test:
	docker compose exec web php tests/run.php

rebuild:
	docker compose build --no-cache web

fresh:
	docker compose down -v && docker compose up -d

db:
	docker compose exec db mysql -uappuser -papppassword contact_form