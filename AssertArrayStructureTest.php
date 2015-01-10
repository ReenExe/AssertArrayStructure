<?php

require_once 'StructureDiffInfo.php';
require_once 'AssertArrayStructure.php';

class AssertArrayStructureTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider simpleTypeProvider
     */
    public function testSimpleTypeSuccess($data, $structure)
    {
        $this->assertArrayStructureSuccess($data, $structure);
    }

    public function simpleTypeProvider()
    {
        return [
            [1,         'integer'],
            [null,      'integer|null'],

            ['text',    'string'],
            [null,      'string|null'],

            [1.0,       'double'],
            [null,      'double|null'],

            [[],        'array'],
            [[1, 2, 3], 'array'],
            [[1, ''],   'array'],
            [null,      'array|null'],

            [true,      'boolean'],
            [null,      'boolean|null'],

            [1,         'string|integer'],
            ['',        'string|integer'],
            [true,      'string|integer|boolean'],
            [[],        'string|integer|boolean|array'],
        ];
    }

    /**
     * @dataProvider simpleTypeFailProvider
     */
    public function testSimpleTypeFail($data, $structure)
    {
        $this->assertArrayStructureFail($data, $structure);
    }

    public function simpleTypeFailProvider()
    {
        return [
            ['',         'integer'],
            [1,          'array'],
            [[],         'string|null'],
        ];
    }

    /**
     * @dataProvider arrayStructureSuccessProvider
     */
    public function testArrayStructureSuccess($data, $structure)
    {
        $this->assertArrayStructureSuccess($data, $structure);
    }

    public function arrayStructureSuccessProvider()
    {
        return [

            /* ~ */
            [
                [1, 2, 3],

                ['values' => 'integer']
            ],

            /* ~ */
            [
                array_merge(
                    range(1, 100),
                    range('a', 'z'),
                    [true, false]
                ),

                ['values' => 'integer|string|boolean']
            ],

            /* ~ */
            [
                [],

                [
                    'values' => [
                        'id'    => 'integer',
                        'name'  => 'string'
                    ]
                ]
            ],

            /* ~ */
            [
                [
                    [
                        'id'    => 3,
                        'name'  => 'Anna'
                    ],

                    [
                        'id'    => 5,
                        'name'  => 'Alex'
                    ],
                ],

                [
                    'values' => [
                        'id'    => 'integer',
                        'name'  => 'string'
                    ]
                ]
            ],

            /* ~ */
            [
                'men',
                [
                    'set' => ['men', 'women', 'unisex']
                ]
            ],

            /* ~ */
            [
                ['men', 'women'],
                [
                    'set' => ['men', 'women', 'unisex']
                ]
            ],

            /* ~ */
            [
                [
                    'status' => 200
                ],
                [
                    'assoc' => [
                        'status' => [
                            'set' => 200
                        ]
                    ]
                ]
            ],

            /* ~ */
            [
                [
                    'id'    => 1,
                    'name'  => 'Alex',

                    'friends' => [
                        [
                            'id'    => 3,
                            'name'  => 'Anna',
                        ],

                        [
                            'id'    => 7,
                            'name'  => 'Bob',
                        ],
                    ]
                ],

                [
                    'assoc' => [
                        'id'    => 'integer',
                        'name'  => 'string',
                        'friends' => [
                            'values' => [
                                'id'    => 'integer',
                                'name'  => 'string',
                            ]
                        ]
                    ]
                ]
            ],

            /**
             * README
             */
            [
                json_decode(
                    <<<JSON
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
                        "social": ["GitHub", "LinkedIn" ]
                    }
JSON
                , true),
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
            ],

        ];
    }

    /**
     * @dataProvider arrayStructureDiffProvider
     */
    public function testArrayStructureDiff($data, $structure, $message, $path)
    {
        $diff = AssertArrayStructure::check($data, $structure);

        $this->assertTrue(
            $this->compareDiff($diff, $message, $path)
        );
    }

    private function compareDiff($diff, $message, $path)
    {
        return ($diff instanceof StructureDiffInfo)
            && $diff->getMessage() === $message
            && $diff->getPath() === $path;
    }

    public function arrayStructureDiffProvider()
    {
        return [

            /* ~ */
            [
                true, 'integer', StructureDiffInfo::TYPE, 'var:type'
            ],

            /* ~ */
            [
                true, null, StructureDiffInfo::CONFIG, 'undefined:structure'
            ],

            /* ~ */
            [
                [], // diff
                [
                    'assoc' => [
                        'id'    => 'integer'
                    ]
                ],
                StructureDiffInfo::KEY,
                'id'
            ],

            /* ~ */
            [
                [
                    'id'    => true // diff
                ],
                [
                    'assoc' => [
                        'id'    => 'integer'
                    ]
                ],
                StructureDiffInfo::TYPE,
                'id.var:type'
            ],

            /* ~ */
            [
                [
                    'id'    => 1,
                    'children' => [
                        1 => [
                            'id' => 3,
                            'children' => []
                        ],

                        2 => [
                            'id' => 5,
                            'children' => null
                        ],

                        3 => [] // diff
                    ]
                ],
                [
                    'assoc' => [
                        'id'    => 'integer',
                        'children' => [
                            'values' => [
                                'id' => 'integer',
                                'children' => 'array|null'
                            ]
                        ]
                    ]
                ],
                StructureDiffInfo::KEY,
                'children.[3].id'
            ],

            /* ~ */
            [
                [
                    'id'    => 1,
                    'children' => [
                        [] // diff
                    ]
                ],
                [
                    'assoc' => [
                        'id'    => 'integer',
                        'children' => [
                            'assoc' => [
                                'id' => 'integer',
                                'children' => 'array|null'
                            ]
                        ]
                    ]
                ],
                StructureDiffInfo::KEY,
                'children.id'
            ],

            /* ~ */
            [
                [
                    'id'    => 1,
                    'name'  => 'Jerry',

                    'bestFriend' => [
                        'id'    => '7', // diff
                        'name'  => 'Tom',
                    ]
                ],

                [
                    'assoc' => [
                        'id'    => 'integer',
                        'name'  => 'string',

                        'bestFriend' => [
                            'assoc' => [
                                'id'    => 'integer',
                                'name'  => 'string',
                            ]
                        ]
                    ]
                ],
                StructureDiffInfo::TYPE,
                'bestFriend.id.var:type'
            ],

            [
                [true], //diff
                ['values' => 'string'],
                StructureDiffInfo::TYPE,
                'array:values'
            ],

            /* ~ */
            [
                'boy',
                [
                    'set' => ['men', 'women', 'unisex']
                ],
                StructureDiffInfo::TYPE,
                'set:out'
            ],

            /* ~ */
            [
                '200',
                [
                    'set' => [200, 500]
                ],
                StructureDiffInfo::TYPE,
                'var:type'
            ],

            /* ~ */
            [
                ['boy', 'girl'],
                [
                    'set' => ['men', 'women', 'unisex']
                ],
                StructureDiffInfo::TYPE,
                'set:out'
            ],

            [
                [true],
                [/*
                 * undefined structure
                */],
                StructureDiffInfo::CONFIG,
                'structure:type'
            ],
        ];
    }

    private function assertArrayStructureSuccess($data, $structure)
    {
        $this->assertTrue(AssertArrayStructure::check($data, $structure));
    }

    private function assertArrayStructureFail($data, $structure)
    {
        /**
         * Ошибка возвращаются в формате массива
         */
        $diff = AssertArrayStructure::check($data, $structure);

        $this->assertTrue(
            $diff instanceof StructureDiffInfo
        );
    }

    private function assertCustomSuccess($data, $structure, $custom)
    {
        $this->assertTrue(AssertArrayStructure::check($data, $structure, $custom));
    }

    /**
     * @dataProvider customProvider
     */
    public function testCustom($data, $structure, $custom)
    {
        $this->assertCustomSuccess($data, $structure, $custom);
    }

    public function customProvider()
    {
        return [

            [
                [
                    'id' => 1,
                    'name' => 'Alex'
                ],
                'profile',
                [
                    'profile' => [
                        'assoc' => [
                            'id' => 'integer',
                            'name' => 'string'
                        ]
                    ]
                ]
            ],

            /* recursion from `README` */
            [
                [
                    'value' => 1,
                    'next' => [
                        'value' => 3,
                        'next' => [
                            'value' => 5,
                            'next' => null
                        ]
                    ]
                ],
                'link',
                [
                    'link' => [
                        'assoc' => [
                            'value' => 'integer',
                            'next'  => 'link|null'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @dataProvider customDiffProvider
     */
    public function testCustomFail($data, $structure, $custom, $message, $path)
    {
        $diff = AssertArrayStructure::check($data, $structure, $custom);

        $this->assertTrue(
            $this->compareDiff($diff, $message, $path)
        );
    }

    public function customDiffProvider()
    {
        return [
            [
                [
                    'id' => 1,
                    'name' => 'Alex',
                    // diff
                ],
                'profile',
                [
                    'profile' => [
                        'assoc' => [
                            'id' => 'integer',
                            'name' => 'string',
                            'enabled' => 'boolean'
                        ]
                    ]
                ],
                StructureDiffInfo::KEY,
                'custom:type:profile.enabled'
            ],

            /* recursion */
            [
                [
                    'name' => 'Mike',
                    'referral' => [
                        'name' => 'Bob',
                        'referral' => false, // diff
                    ]
                ],
                'referralLink',
                [
                    'referralLink' => [
                        'assoc' => [
                            'name' => 'string',
                            'referral' => 'referralLink|null'
                        ]
                    ]
                ],
                StructureDiffInfo::TYPE,
                'custom:type:referralLink.referral.'

                . 'custom:type:referralLink.referral.'

                . 'custom:type:referralLink.var:type'
            ]
        ];
    }

    public function testGlobalCustom()
    {
        $profileRuby = [
            'id' => 7,
            'name' => 'Ruby'
        ];

        $diff = AssertArrayStructure::check($profileRuby, 'profile');

        $this->assertTrue(
            $diff instanceof StructureDiffInfo
        );

        $customProfile = [
            'profile' => [
                'assoc' => [
                    'id' => 'integer',
                    'name' => 'string'
                ]
            ]
        ];

        AssertArrayStructure::addCustom($customProfile);

        $this->assertTrue(
            AssertArrayStructure::check($profileRuby, 'profile')
        );
    }
}