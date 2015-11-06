DueDil Test Project
========================

To run the project go to its root directory and run:

- Generate a GitHub user token at: https://github.com/settings/tokens
- Copy the token and add it to: app/config/config.yml at <TOKEN_REPLACE_ME>
- Add your GitHub username at: <USERNAME_REPLACE_ME>
- php app/console server:start
- load http://127.0.0.1:8000/gitPath/tedivm/icio
- another example: http://127.0.0.1:8000/gitPath/willdurand/jhallbachner

The result is in JSON format as follows:

```JSON
{

    "repository": "https://github.com/tedious/Stash.git",
    "package": "tedivm/stash",
    "userOne": "icio",
    "userTwo": "tedivm"

}
```

Requirements 
------------
NOTE: **The application requires APC to be installed on the machine.**
It's used to cache the response data from the HTTP requests to improve performance
and reduce GitHub requests.