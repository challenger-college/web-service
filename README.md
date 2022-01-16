# Challenger

## Getting start
```
composer install
bin/console d:s:u --force
php -S 0.0.0.0:8000 -t public
``` 

## API endpoints
Use token API (found in `.env`) in POST or GET request for authenticate yourself.

* List of challenges not validated [/api/challenges?token={TOKEN}](localhost:8000/api/challenges?token=<TOKEN>).
* Edit challenge validity and post challenge template [/api/challenge/{challenge_id}/check?token={TOKEN}](localhost:8000/api/challenge/{challenge_id}/check?token={TOKEN})
    * parameters:
        * isValid (boolean)
        * template (text)
* List of exercices submited and not validated [/api/exercices?token={TOKEN}](localhost:8000/api/exercices?token={TOKEN})
* Edit exercice validation [/api/exercice/{exercice_id}/check?token={TOKEN}](localhost:8000/api/exercice/{exercice_id}/check?token={TOKEN})
    * parameters:
        * isValid (boolean)