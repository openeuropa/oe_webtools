# OpenEuropa Webtools Laco Service

A Webtools Laco service that provides information on whether entity has a translation in a specific language.

### How it works

This module exposes a new route for each entity type that "mirrors" the canonical route of entities. These routes have 
the same paths as the canonical entity routes but use different requirements for matching. If these requirements
are matched in the request, the module will return Laco information for that entity.

### How to test

#### Step 1

Enable the module.

#### Step 2

Create a node (or entity of a type which exposes a canonical path, such as `node/1`).

#### Step 3

Make a request to this path with the following specifications:

* Request method: `HEAD`.
* `EC-Requester-Service` header with the value `WEBTOOLS LACO`.
* `EC-LACO-lang` header with the value of the language code you want coverage information for.

#### Step 4

Interpret the response:

* `200 OK` -> there is a translation for this entity in the requested language
* `404 Not found` -> there is no translation for this entity (or the entity is missing)
* `403 Forbidden` -> access is denied to that resource