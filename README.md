CaesarApp Server
==========

[![Build Status](https://travis-ci.org/4xxi/caesarapp-server.svg?branch=master)](https://travis-ci.org/4xxi/caesarapp-server)

## Installation Instructions

### Requirements

* [Docker and Docker Compose](https://docs.docker.com/engine/installation)
* [MacOS Only]: Docker Sync (run `gem install docker-sync` to install it)

### Configuration

Application configuration is stored in `.env` file.

#### HTTP port
If you have nginx or apache installed and using 80 port on host system you can either stop them before proceeding or
reconfigure Docker to use another port by changing value of `SERVER_HTTP_PORT` in `.env` file.

#### Application environment
You can change application environment to `dev` of `prod` by changing `APP_ENV` variable in `.env` file.

#### Redis
Redis credentials could by reconfigured by changing variable `REDIS_URL`. It is
recommended to restart containers after changing these values.

### Installation

#### 1. Start Containers and install dependencies
On Linux:
```bash
docker-compose -f docker-compose-dev.yml up -d
```
On MacOS:
```bash
docker-sync-stack start
```
#### 2. Open project
Just go to [http://localhost](http://localhost)

## API documentation

### Message Entity

The app stores each message in redis. `Message` has the following fields:

* **id** (string) is used for getting the message. It is generated automatically after `POST` request.
* **message** (text) contains encrypted body of the message including any file attached.
* **requestsLimit** (integer) defines how many times the message can be decrypted.
* **secondsLimit** (integer) defines how long the message can be decrypted. After `secondsLimits` seconds the message will be permanently destroyed.
* **expires** (date in RFC) provides exact date & time when the message is destroyed.

### API calls

#### Base URL

Base URL for the API is `yourdomain.org/api`. All endpoints have prefix `/api`.

#### Error format

If any error appears the response body would automatically get an element with key `errors`. This element has an array with the keys equal to the fields that have caused the error. Each field is also an array of string variables representing errors in text format.

Please note that in case of the error the `status code` is changed to `400` (for POST /messages request) or to `404` (GET /messages/{id})

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

**Important:** the data should be sent with POST request with raw data in json format. An example of the raw request body is the following:

```json
{
    "message":"text",
    "secondsLimit":10,
    "requestsLimit":5
}
```

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

If a message is not found the response has the status code `404` and the following error appears:

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

## Other

### Misc

* Please note that the `requestsLimit` variable won't be changed in the response due to the security reasons (in other words: a user should not know how many tries are left).
* Each successful request to `GET /messages/{id}` decreases by 1 the number of attempts. When it is equal to zero the message will be destroyed.

### Dev Notes

#### Checking code style and running tests
Fix code style by running PHP CS Fixer:
```bash
docker-compose exec php vendor/bin/php-cs-fixer fix
```

### Run PHP Unit Tests:
```bash
docker-compose exec php bin/phpunit
```
