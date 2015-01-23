Assert Structure
====================

[![Build Status](https://travis-ci.org/ReenExe/AssertArrayStructure.svg)](https://travis-ci.org/ReenExe/AssertArrayStructure)

Native PHP assert for testing REST API JSON response

Example:
------------
You have REST API method that return response like:
```json
{
    "id": 3,
    "name": "Alex",
    "location": 3,
    "gender": "men",
    "joined": {
        "source": 1,
        "at": "2011-11-11 11:11:11"
    },
    "friends": [
        {
            "id": 7,
            "name": "Alice"
        },
        {
            "id": 8,
            "name": "Bob"
        }
    ],
    "interests": ["programming", "books", "sport"],
    "games": null,
    "books": [
        {
            "author": "Достоевский Фёдор Михайлович",
            "title": "Преступление и наказание"
        },
        {
            "author": "Steve McConnell",
            "title": "Code Complete"
        }
    ],
    "social": ["GitHub", "LinkedIn"]
}
```
And we want to check these structures through the settings:
```php
[
    'assoc' => [
        'id'    => 'integer',
        'name'  => 'string',
        'location'  => 'integer',
        'gender'    => [
            'set' => ['men', 'women', null]
        ],
        'joined'    => [
            'assoc' => [
                'source'    => 'integer|null',
                'at'        => 'string'
            ]
        ],
        'friends' => [
            'type' => 'null',
            'values' => [
                'id'    => 'integer',
                'name'  => 'string'
            ]
        ],
        'interests' => [
            'type' => 'null',
            'values' => 'string'
        ],
        'games' => [
            'type'  => 'null',
            'values' => [
                'title' => 'string'
            ]
        ],
        'books' => [
            'values' => [
                'author' => 'string',
                'title'  => 'string',
            ]
        ],
        'social' => [
            'set' => [
                'GitHub', 'LinkedIn', 'Facebook', 'Google', 'Twitter',
            ]
        ]
    ]
]
```
In this example, tried to describe the full scope

Also:
------------
We can test the simple types
```json
1
```
```php
'integer'
```
or
```php
array_merge(
    range(1, 100),
    range('a', 'z'),
    [true, false]
)
```
```php
['values' => 'integer|string|boolean']
```

Use:
------------
```php
/**
 * @return StructureDiffInfo
 */
AssertArrayStructure::check($data, $structure)

/**
 * @method public bool StructureDiffInfo::isEqual
 * @method public string StructureDiffInfo::getMessage
 * @method public string StructureDiffInfo::getPath
 */
```
in PHPUnit easy:
```php
...
public function assertArrayStructureSuccess($data, $structure)
{
    $diff = AssertArrayStructure::check($data, $structure);

    $this->assertTrue($diff->isEqual(), (string) $diff);
}
...
```

Custom types (or `user types`):
------------
We can set user-defined types once and use them on:
```php
    AssertArrayStructure::addCustom(array $custom)
```
Example:
```php
AssertArrayStructure::addCustom(                [
    'profile' => [
        'assoc' => [
            'id' => 'integer',
            'name' => 'string'
        ]
    ]
]);
...
AssertArrayStructure::check($profile, 'profile');
...
AssertArrayStructure::check($response, 'profile');
...
```
And also to establish the types of one-time inspection:
```php
AssertArrayStructure::check(
    $data = [
        'value' => 1,
        'next' => [
            'value' => 3,
            'next' => [
                'value' => 5,
                'next' => null
            ]
        ]
    ],
    $structure = 'link',
    $custom = [
        'link' => [
            'assoc' => [
                'value' => 'integer',
                'next'  => 'link|null'
            ]
        ]
    ]
);
```
In `user types` - possibility of recursive checks

`@TODO` or to be continued...
------------

Together with the structures of `assoc`,` values`, `set` - there is a desire to add structure `range`

Alternatives:
------------
AssertJsonStructure: https://github.com/t4web/AssertJsonStructure
    - part of it was used to create, also `used idea of checking the structure`
    
json-schema: https://github.com/justinrainbow/json-schema