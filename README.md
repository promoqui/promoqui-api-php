# PromoQui API Wrapper for PHP

This library is for internal use only. It serves as an helper for Ruby developers when interacting with the PromoQui REST API for Crawlers.

The Settings class allows you to set the URL of the API domain.

You should not attempt to use this library against the official PromoQui API domain without permission from PromoQui SPA. Feel free however, to use it for your own projects and to report any problem you may find.

# Setup

First of all, you have to declare the `Crawlers` namespace and import the PQSDK loader using php's require function:
```php 
namespace Crawlers;

require 'PQSDK.php';
```

Configure the library to use your provided api host, key, schema and country:

```php
Settings::$country = 'it';
Settings::$host = 'api.promotest.dev';
Settings::$app_secret = 'sup3rs3cr3t';
Settings::$schema = 'http'; // or https
```

The PromoQui REST API requires the app secret to be exchanged with a token with a duration of 3 hours. All the Token exchange and renovation is handled internally by the library, so that you can completely ignore it.

# Working with brands

```php
$brand = Brand::find('Apple');
```

That line of code will interrogate the PromoQui database for that Brand and will return a Brand object, containing details like: id, name, slug.

```php
$brands = Brand::list();
```

That line of code will interrogate the PromoQui database for that all brands and will return Brand objects array.

# Working with cities

```php
$city = City::find('Rome');
```
That line of code will interrogate the Promoqui database for the given city. If the city exists on our database, it will return a City object all details about the given city such as: name, latitude, longitude, inhabitants and most importantly the City ID.

```php
$city = City::find_or_create('Rome');
```

That line of code will interrogate the PromoQui database for that City, eventually creating it if not found, and will return a City object, containing all the details like: latitude, longitude, inhabitants and most importantly the City ID.

# Working with stores

```php
$store = Store::find('Via Roma, 32', '80100');
if ($store == null){
  $store = new Store(); 
  $store->$name = "Store name"; # Required!
  $store->$address = "Via Roma, 32"; # Required!
  $store->$city = "Naples"; # if the city is not present on database then the city will be created. Required!
  $store->$latitude = ""; # insert the store's latitude. Required!
  $store->$longitude = "";# insert the store's longitude. Required!
  $store->$zipcode = ""; # insert the store's postalcode. if there is no postalcode, insert "00000". Required!
  $store->$origin = ""; # insert the store's url. Required!
  $store->$phone = "";# insert the store's phone if present
}
$store->$opening_hours = [store_hours]; # Insert the store's opening hours as array. Required!
$store->save(); # Save store's data
```

>##Note!##
>The opening hours array must be as follow:
>```php
>[["weekday"=>0, "open_am"=>"09:00", "close_am"=>"13:00", "open_pm"=>"14:00", "close_pm"=>"18:00"], ...]
> If the store is closed you need to use such as: [ ["weekday"=>6, "closed"=>true] ]
>```


That code will interrogate the database for a store at that address, with that zipcode, among the stores for the retailer we were assigned. If the store was not found then we will set all data of store and then save it.

# Working with leaflets

```php
$leaflet = Leaflet::find($url);
if ($leaflet == null) {
  $leaflet = new Leaflet();
  $leaflet->$name = 'Nice leaflet';
  $leaflet->$url = $url;
  $leaflet->$store_ids = [ $storeIds];
  $leaflet->save();
}
```

That code will try to find a leaflet with the same url (to avoid inserting it again). If it is not found it will create a new leaflet. Pay attention at the `store_ids` field. It must be an array of valid store ids, for which the leaflet is valid.

A few seconds after the creation, the PromoQui infrastructure will begin to parse and upload the leaflet pages to the website.

If you do not dispose of a valid GET url from which the leaflet can be taken, but you are however able to obtain a binary version of the leaflet (raw PDF data bytes), you can still upload it like this:

```php
$leaflet = new Leaflet();
$leaflet->$url = $url; # Set to a significant URL to avoid repetitions
$leaflet->$name = "Nice leaflet";
$leaflet->$store_ids = [ $storeIds ];
$leaflet->$pdf_data = $binary_blob;
$leaflet->save();
```

#Working with offers

For each offer we need to parse:
  * Offer's title
  * Offer's description
  * Offer's url
  * Offer's image url
  * Offer's price
  * Offer's original price _if present_
  
Suppose we have all offers saved in an array called `offers` and an array called `storeIds` that contains all store ids:
```php
foreach($offers as $data){
  $data["store_ids"] = $storeIds; #add store ids to offer array
  $offer = new Offer($data);
  $offer->save();
}
```
With the above code we scroll all offers, asing storeIds to offer and save it.

# Have a nice day
