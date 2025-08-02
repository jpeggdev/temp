<?php

namespace App\DoctrineFunctions;

use Doctrine\ORM\Query\AST\ASTException;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

class IRegexFunction extends FunctionNode
{
    public Node $field;
    public Node $pattern;

    /**
     * Returns the SQL for the REGEX function with a field and a pattern.
     *
     * Example usages:
     * 1. Matching a field against a regex pattern:
     *    $queryBuilder->select('REGEX(p.name, \'^abc\')')
     *
     * 2. Using REGEX with a different field and pattern:
     *    $queryBuilder->select('REGEX(p.description, \'^xyz\')')
     *
     * 3. Checking if a string matches the pattern:
     *    $queryBuilder->select('REGEX(p.address, \'^123\')')
     *
     * @throws QueryException
     */
    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->field = $parser->StringPrimary();

        $parser->match(TokenType::T_COMMA);

        $this->pattern = $parser->StringPrimary();

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    /**
     * @throws ASTException
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        return $this->field->dispatch($sqlWalker) . ' ~* ' . $this->pattern->dispatch($sqlWalker);
    }
}
