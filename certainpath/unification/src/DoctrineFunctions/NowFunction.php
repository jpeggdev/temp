<?php

namespace App\DoctrineFunctions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\TokenType;

class NowFunction extends FunctionNode
{
    /**
     * Parses the DQL query for the NOW() function
     *
     * Example usages:
     *
     * 1. Selecting the current timestamp:
     *    $queryBuilder->select('NOW() AS currentTime')
     *
     * 2. Filtering records based on the current date/time:
     *    $queryBuilder->where('u.expirationDate < NOW()')
     *
     * @throws QueryException
     */
    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return 'NOW()';
    }
}
