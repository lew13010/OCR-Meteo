# OCR-Meteo
[Projet] Lancer votre application météo

## Installation

#### Composer 

```
composer require lew/meteobundle
```

#### Database


```
doctrine:database:create
```


```
doctrine:schema:update --force
```

#### Assets

```
assets:install --symlink 
```


## Configuration

Add these lines in your file *config/parameters.yml* with your choices :

```
api_keys: #key api openweathermap
units: metric # metric for Celsius or imperial for Farenheit 
langs: fr # fr=french or en=english or...
```