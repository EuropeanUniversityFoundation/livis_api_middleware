# LIVIS middleware project

This project contains a module for Drupal 9+, that acts as a middleware for accessing the LIVIS project API.

## Quick start

In order to install this module:
  - download the files and add them in your site's `web/modules/custom/livis_api_middleware` directory
  - or add the following vcs to your composer json and install it via composer `composer require euf/livis_api_middleware`
```
    {
      ...
      "repositories": [
        ...

        {"type": "vcs", "url": "https://github.com/EuropeanUniversityFoundation/livis_api_middleware/"},
      ],
      ...
    }
```

Once installed, enable the module in Drupal on the admin ui or if you have Drush, type `drush en livis_api_middleware`

## Setting up the module

Put the LIVIS API endpoint URLs and credentials into your `local.settings.php`. By default you can find the settings file at `web/sites/default/local.settings.php`. Paste the following keys and values to the end of that file:
- `$settings['livis_api']['login']['base_uri']`: The base URL of the authentication endpoint. Example: `https://login.livis.eu`
- `$settings['livis_api']['login']['path']`: The subpath of the athentication endpoint. Example: `/login`. This means, the full path of the authentication endpoint will be: `https://login.livis.eu/login`
- `$settings['livis_api']['credentials']['username']`: The username to log in with on the authentication endpoint.
- `$settings['livis_api']['credentials']['password']`: The password to use with the username on the authentication endpoint.
- `$settings['livis_api']['base_uri']`: The base URL of the LIVIS inventory API endpoint. Example: `https://inventory.livis.eu`
- `$settings['livis_api']['statistics']['path']`: The subpath of the LIVIS living cost statistics API endpoint. Example: `/living_cost_statistics`. This means, the full path of the statistics endpoint will be: `https://api.livis.eu/statistics`
- `$settings['livis_api']['cities']['path']`: The subpath of the LIVIS API cities endpoint. Example: `/cities`
- `$settings['livis_api']['submission']['path']`: The subpath of the LIVIS API submissions endpoint. Example: `/submissions`
- `$settings['livis_api']['page_content']['path']`: The middleware provides the content of two pages as an endpoint. Example: `pages`

## Endpoints
The module adds endpoints to the site, that use the credentials, urls and paths to first login to the LIVIS API, store the JWT token in temporary storage and then call the LIVIS API's corresponding endpoint using the retrieved token to fetch data.

### Filterable statistics collection
- Path: `/livis/living_cost_statistics`
- Method: `GET`
- Optional parameters (query): `city`, `city.name`
#### Examples of use
- `{site_url}/livis/statistics?city=/cities/1` where `/cities/1` is the IRI from the response in the cities endpoint.
- `{site_url}/livis/statistics?city.name=Brussels` where `city.name` is the name of the city.

### Statistics resource
  - Path: `/livis/living_cost_statistics/{id}`
  - Method: `GET`
  - Path parameter: `id`
#### Example usage
- `{site_url}/livis/living_cost_statistics/1` (where `{id} = 1`). Response contains the living cost statistics with the `id`: 1.

### Cities endpoint
  - Path: `/livis/cities`
  - Method: `GET` and `POST`
  - Parameters (query): `name` (optional)
#### Example usage (GET)
  - `{site_url}/livis/cities?name=City name`
#### Example usage (POST)
- `{site_url}/livis/cities` with a JSON body
```json
{
  "name":"City name",
  "country":"Country name"
}
```
This creates a city in the LIVIS API with the posted data.

### Living cost submissions endpoint
  - Path: `/livis/living_cost_submissions`
  - Method: `POST`
  - Parameters: None
#### Example usage (POST)
-`{site_url}/livis/living_cost_submissions` with a JSON body:
```json
{
  "monthlyAccommodationCost": 500,
  "monthlyLivingCost": 500,
  "userId": 2,
  "city": "/cities/2",
  "stayDurationInMonths": 4,
  "termsAccepted": "On"
}
```
- userId: is the id of the user in the external system.
- city: the IRI of the city from the response in the cities endpoint.
- termsAccepted: If the value is not `"On"`, the submission is rejected.

## Permissions and Authentication
The module provides the `use livis_api_middleware` permission, assign it to ALL AUTHENTICATED users. The client has to take care of authenticating the Drupal users. Currently  `cookie` authentication is enabled for the endpoint (in the routing file).

## Configuration provided (views)
There are two views in the `config/optional` folder of the module. These have dependencies, they're only installed if those are met. Please check if they are installed when the module is activated! Their names are the following:
  - ERA Cities extended
  - Living cost form submissions extended
