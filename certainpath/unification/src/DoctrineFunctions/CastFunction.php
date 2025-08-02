<?php

namespace App\DoctrineFunctions;

use Doctrine\ORM\Query\AST\ASTException;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

class CastFunction extends FunctionNode
{
    public string $type;
    public Node $field;

    /**
     * Parses the DQL query for the CAST function with dynamic type
     *
     *  Example usages:
     *
     *  1. Casting to Numeric:
     *     $queryBuilder->select('CAST(p.value AS NUMERIC)')
     *
     *  2. Casting to Date:
     *     $queryBuilder->select('CAST(p.dateField AS DATE)')
     *
     *  3. Casting to Integer:
     *     $queryBuilder->select('CAST(p.value AS INTEGER)')
     *
     *  4. Casting to String:
     *     $queryBuilder->select('CAST(p.value AS STRING)')
     *
     * @throws QueryException
     */
    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->field = $parser->ArithmeticExpression();

        $parser->match(TokenType::T_IDENTIFIER);

        $this->type = $parser->getLexer()->lookahead->value;

        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    /**
     * @throws ASTException
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        return 'CAST(' . $this->field->dispatch($sqlWalker) . ' AS ' . $this->type . ')';
    }
}

