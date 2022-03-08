# Serato Web Services PHP App Bootstrap

Common functionality for building Serato Web Services (SWS) PHP web applications.

## Using Docker to develop this library.

Use the provided [docker-compose.yml](./docker-compose.yml) file to develop this library.

```bash
# Run the `php-build` service using the default PHP version (7.1) and remove the container after use.
docker-compose run --rm  php-build

# Provide an alternative PHP version via the PHP_VERSION environment variable.
PHP_VERSION=7.2 docker-compose run --rm  php-build
```

When Docker Compose runs the container it executes [docker.sh](./docker.sh).

This script installs some required packages, installs [Composer](https://getcomposer.org/) and performs a `composer install` for this PHP library.

It then opens a bash shell for interacting with the running container.

### AWS credentials for integration tests

To run integration tests that interact with AWS services provide an IAM access key and secret via the `AWS_ACCESS_KEY_ID` and `AWS_SECRET_ACCESS_KEY` environment variables.

```bash
AWS_ACCESS_KEY_ID=my_key_id AWS_SECRET_ACCESS_KEY=my_key_secret docker-compose run --rm  php-build
```