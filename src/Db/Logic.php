<?php

namespace EasyDb;

enum Logic:string
{
    /** 等于 */
    case EQ = '=';
    /** 不等于 */
    case NE = '<>';
    /** 大于等于 */
    case GE = '>=';
    /** 大于 */
    case GT = '>';
    /** 小于等于 */
    case LE = '<=';
    /** 小于 */
    case LT = '<';
    /** 包含 */
    case IN = 'IN';
    /** 相似 */
    case LIKE = "LIKE";


}