# LIVIS middleware project

This project contains a module for Drupal 9+, that acts as a middleware for accessing the LIVIS project API.

## Quick start

In order to install this module:
  - download the files and add them in your site's `web/modules/custom/livis_api_middleware` directory
  - or add the vcs to your composer json and install it via composer `composer require euf/livis_api_middleware`

Once installed, enable the module in Drupal on the admin ui or if you have Drush, type `drush en livis_api_middleware`

## Setting up the module

Put the LIVIS API endpoint URLs and credentials into your `local.settings.php`. By default you can find the settings file at `web/sites/default/local.settings.php`. Paste the following keys and values to the end of that file:
  - `$settings['livis_api']['login']['base_uri']`: The base URL of the authentication endpoint. Example: `https://login.livis.eu`
  - `$settings['livis_api']['login']['path']`: The subpath of the athentication endpoint. Example: `/login`. This means, the full path of the authentication endpoint will be: `https://login.livis.eu/login`
  - `$settings['livis_api']['credentials']['username']`: The username to log in with on the authentication endpoint.
  - `$settings['livis_api']['credentials']['password']`: The password to use with the username on the authentication endpoint.
  - `$settings['livis_api']['base_uri']`: The base URL of the LIVIS inventory API endpoint. Example: `https://inventory.livis.eu`
  - `$settings['livis_api']['statistics']['path']`: The subpath of the LIVIS living cost statistics API endpoint. Example: `/statistics`. This means, the full path of the statistics endpoint will be: `https://api.livis.eu/statistics`
  - `$settings['livis_api']['cities']['path']`: The subpath of the LIVIS API cities endpoint. Example: `/cities`
  - `$settings['livis_api']['page_content']['path']`: The middleware provides the content of two pages as an endpoint. Example: `pages`

## Endpoints
The module adds endpoints to the site, that use the credentials, urls and paths to first login to the LIVIS API, store the JWT token in temporary storage and then call the LIVIS API's corresponding endpoint using the retrieved token to fetch data.

### Statistics endpoint
  - Path: `/livis/statistics`
  - Method: `GET`
  - Parameters (query): `city`, `city.name`
  - Examples of use:
    - `{site_url}/livis/statistics?city=/cities/1` where `/cities/1` is the IRI from the response in the cities endpoint
    - `{site_url}/livis/statistics?city.name=Brussels` where `city.name` is the name of the city.

### Providers endpoint
  - Path: `/livis/cities`
  - Method: `GET` and `POST`
  - Parameters: None
  - Example usage (GET): `{site_url}/livis/cities`
  - Example usage (POST): `{site_url}/livis/cities` with a JSON body `{"name":"City name", "country":"Country name"}`

## Permissions and Authentication
The module provides the `use livis_api_middleware` permission, assign it to the roles that should be able to access the endpoint. The client has to take care of authenticating the Drupal users. Currently `api_key` and `cookie` authentication is enabled for the endpoint (in the routing file).

## Configuration provided (views)
There are two views in the `config/optional` folder of the module. These have dependencies, they're only installed if those are met. Please check if they are installed when the module is activated! Their names are the following:
  - ERA Cities extended
  - Living cost form submissions extended
