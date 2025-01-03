#!/bin/sh

image="cybrarist/discount-bandit"
version=$1


php artisan migrate:fresh --force
rm storage/debugbar/*
rm -r storage/framework/cache/*
rm storage/framework/sessions/*
rm storage/logs/*


docker build --platform linux/amd64,linux/arm64 -t "$image:v$version" .
docker build --platform linux/amd64,linux/arm64 -t "$image:latest" .



docker push "$image:v$version"
docker push "$image:latest"
