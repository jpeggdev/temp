<?php

namespace App\DoctrineFunctions;

use Doctrine\ORM\Query\AST\ASTException;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

class ExtractFunction extends FunctionNode
{
    private string $unit;
    private Node $field;

    /**
     * Parse the DQL for the EXTRACT function.
     *
     * Example usage:
     * EXTRACT(YEAR FROM NOW())
     *
     * @param Parser $parser
     * @throws QueryException
     */
    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->unit = $parser->getLexer()->lookahead->value;
        $parser->match(TokenType::T_IDENTIFIER);

        $parser->match(TokenType::T_FROM);
        $this->field = $parser->ArithmeticPrimary();

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    /**
     * @throws ASTException
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        return sprintf(
            "EXTRACT(%s FROM %s)",
            $this->unit,
            $this->field->dispatch($sqlWalker)
        );
    }
}
