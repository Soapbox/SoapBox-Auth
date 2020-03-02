# SoapBox-Auth

## Description

This Auth Service was designed to help with managing auth across all SoapBox services.
In particular, it allowed for login and logout through JWT and using REDIS as a datastore (speed reasons). \
View the functional designs below:

-   [Login](https://docs.google.com/drawings/d/11RYQxCKW18i5xg-Dx1B7nsNAdFt7tSmpJ5epiVAvq6w/edit?usp=sharing)
-   [Logout](https://docs.google.com/drawings/d/1NAEMD1ydYjgKcBniaAqQvvwsYQGAxh4BzARzxHi2Lw4/edit?usp=sharing)

## Dependencies

-   Redis
-   OAUTH PROVIDERS (Clients and secrets)
    -   Google
    -   Slack
    -   Microsoft ...

## Setup instructions

-   create .env file (copy .env.example and tweak as necessary) \
    Be careful to put in correct values for all providers Here are a few pitfalls for some of the `env` variables

    -   URLs:
        -   `http` is different from `https`
        -   `http://url.com` is different from `http://url.com/`
    -   The client, secret and redirect url (used when using legacy auth) must match whatever values used on the frontend \

-   Run `composer install`

## How to use legacy auth:

To use the GoodTalk-API for authentication, simply add a parameter `soapbox-slug` to your request.

## FAQs

-
