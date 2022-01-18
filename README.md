# Challenger

## Getting start
```
composer install
bin/console d:s:u --force
php -S 0.0.0.0:8000 -t public
``` 

## API endpoints
Use token API (found in `.env`) in **POST** or **GET** request for authenticate yourself.

* List of challenges not validated **GET** `/api/challenges?token={TOKEN}]`.
* Edit challenge validity and post challenge template **POST** `/api/challenge/{challenge_id}/check?token={TOKEN}`.
    * parameters:
        * isValid (boolean)
        * template (text)
* List of exercises submited and not validated **GET** `/api/exercises?token={TOKEN}`.
* Edit exercise validation **POST** `/api/exercise/{exercise_id}/check?token={TOKEN}`.
    * parameters:
        * isValid (boolean)