<?php


namespace EasyDb;


interface TableType
{
    const TYPE_INT = [
        'tinyint', 'smallint', 'mediumint', 'int', 'bigint'
    ];
    const TYPE_FLOAT = [
        'float', 'double'
    ];
    const TYPE_DECIMAL = [
        'decimal'
    ];

    const TYPE_CHAR = [
        'char', 'varchar', 'tinytext', 'text', 'mediumtext', 'longtext'
    ];

    const TYPE_BLOB = [
        '_BLOB',
        '_TEXT',
        ''
    ];

    const TYPE_DATE = [
        'date',
        'time',
        'datetime',
        'timestamp',
    ];


}