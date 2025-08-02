<?php

namespace App\DoctrineFunctions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

class SimilarityFunction extends FunctionNode
{
    public $field = null;
    public $searchTerm = null;

    public function getSql(SqlWalker $sqlWalker): string
    {
        return 'similarity(' .
            $this->field->dispatch($sqlWalker) . ', ' .
            $this->searchTerm->dispatch($sqlWalker) . ')';
    }

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $this->field = $parser->StringPrimary();
        $parser->match(TokenType::T_COMMA);
        $this->searchTerm = $parser->StringPrimary();
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }
}