CaesarApp Server
==========

Installation Instructions
==========

## Requirements

* [Docker and Docker Compose](https://docs.docker.com/engine/installation)
* [MacOS Only]: Docker Sync (run `gem install docker-sync` to install it)

## Configuration

Application configuration is stored in `.env` file.

### HTTP port
If you have nginx or apache installed and using 80 port on host system you can either stop them before proceeding or
reconfigure Docker to use another port by changing value of `SERVER_HTTP_PORT` in `.env` file.

### Application environment
You can change application environment to `dev` of `prod` by changing `APP_ENV` variable in `.env` file.

### Redis
Redis credentials could by reconfigured by changing variable `REDIS_URL`. It is
recommended to restart containers after changing these values.

## Installation

### 1. Start Containers and install dependencies
On Linux:
```bash
docker-compose up -d
```
On MacOS:
```bash
docker-sync-stack start
```
### 2. Open project
Just go to [http://localhost](http://localhost)

## Checking code style and running tests
Fix code style by running PHP CS Fixer:
```bash
docker-compose exec php vendor/bin/php-cs-fixer fix
```

### Run PHP Unit Tests:
```bash
docker-compose exec php bin/phpunit
```

## API documentation

### Message Entity

The app stores each message in redis. `Message` has the following fields:

* **id** (string) is used for getting the message. It is generated automatically after `POST` request.
* **message** (text) contains encrypted body of the message including any file attached.
* **requestsLimit** (integer) defines how many times the message can be decrypted.
* **secondsLimit** (integer) defines how long the message can be decrypted. After `secondsLimits` seconds the message will be permanently destroyed.
* **expires** (date in RFC) provides exact date & time when the message is destroyed.

### API calls

#### Error format

If any error appears the response body would automatically get an element with key `errors`. This element has an array with the keys equal to the fields that have caused the error. Each field is also an array of string variables representing errors in text format.

###### Example

```json
{
    "errors":
    {
        "secondsLimit":
        [
            "This value should not be blank.",
            "This value should not be greater than 5"
        ],
        "requestsLimit":
        [
            "This value should not be blank."
        ]
    }
}
```

#### POST /messages

The request is used to create new message.

##### Inputs

* message (required)
* requestsLimit (required)
* secondsLimit (required)

#### Example Output

```json
{
    "id":"745e8728047f7c7970d13d47b2ffb737faa3c9b7",
    "message":"text",
    "expires":"2018-05-12T21:21:48+00:00",
    "requestsLimit":2,
    "secondsLimit":100
}
```

#### GET /messages/{id}

The request is used to get the message with given `id`.

##### Inputs

No

##### Output
```json
{
    "id":"745e8728047f7c7970d13d47b2ffb737faa3c9b7",
    "message":"text",
    "expires":"2018-05-12T21:21:48+00:00",
    "requestsLimit":2,
    "secondsLimit":100
}
```

##### Errors

If a message is not found the following error appears:

```json
{
    "errors":
    {
        "id":"Message not found"
    }
}
```

#### GET /messages

The request can be used to provide the actual status of the API (test request).

##### Inputs

No

##### Output
```json
{
    "status":"OK"
}
```

##### Comments

* Please note that the `requestsLimit` variable won't be changed in the response due to the security reasons (in other words: a user should not know how many tries are left).
* Each successful request to `GET /messages/{id}` decreases by 1 the number of attempts. When it is equal to zero the message will be destroyed.
