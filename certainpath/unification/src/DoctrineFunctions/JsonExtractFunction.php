<?php

namespace App\DoctrineFunctions;

namespace App\DoctrineFunctions;

use Doctrine\ORM\Query\AST\ASTException;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Literal;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

class JsonExtractFunction extends FunctionNode
{
    public Node $jsonKey;
    public Node $jsonColumn;

    /**
     * Parses the DQL query for the JSON_EXTRACT function
     *
     * Example usages:
     *
     * 1. Extracting a value from a JSON column:
     *    $queryBuilder->select('JSON_EXTRACT(p.jsonField, \'name\')')
     *
     * 2. Extracting a nested value from a JSON column:
     *    $queryBuilder->select('JSON_EXTRACT(p.jsonField, \'address.city\')')
     *
     * 3. Extracting an integer value from a JSON column:
     *    $queryBuilder->select('JSON_EXTRACT(p.jsonField, \'age\')')
     *
     * 4. Extracting a boolean value from a JSON column:
     *    $queryBuilder->select('JSON_EXTRACT(p.jsonField, \'isActive\')')
     *
     * @throws QueryException
     */
    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->jsonColumn = $parser->ArithmeticExpression();

        $parser->match(TokenType::T_COMMA);

        $this->jsonKey = $parser->StringPrimary();

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    /**
     * @throws ASTException
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        $jsonKeyValue = $this->jsonKey instanceof Literal ? $this->jsonKey->value : '';

        return sprintf(
            "%s ->> '%s'",
            $this->jsonColumn->dispatch($sqlWalker),
            $jsonKeyValue
        );
    }
}
